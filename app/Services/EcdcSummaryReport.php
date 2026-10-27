<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\District;
use App\Models\Division;
use App\Models\ECDC;
use App\Models\School;
use App\Models\StudentClassroom;
use App\Models\User;
use App\Support\RoleDataScope;
use App\Support\UserHierarchy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EcdcSummaryReport
{
    public const AGES = ['4.1 - 5.0', '5.1 - 5.11', '6.0 - 6.11'];

    public const INTERPRETATIONS = ['Significant delay', 'Slight delay', 'Average', 'Slightly advanced', 'Highly advanced'];

    /**
     * @param  array{scope: 'school'|'district'|'division', scope_id: int, academic_year_id: int, period: int}  $filters
     */
    public function authorize(User $user, array $filters): void
    {
        $hierarchy = app(UserHierarchy::class)->forUser($user);
        $scope = app(RoleDataScope::class);
        $query = match ($filters['scope']) {
            'school' => $scope->schoolsFor($user),
            'district' => in_array($hierarchy['scope'], ['system', 'division', 'district'], true) ? $scope->districtsFor($user) : District::query()->whereRaw('1 = 0'),
            'division' => in_array($hierarchy['scope'], ['system', 'division'], true) ? $scope->divisionsFor($user) : Division::query()->whereRaw('1 = 0'),
        };
        abort_unless($query->whereKey($filters['scope_id'])->exists(), 404);
        abort_if($hierarchy['scope'] === 'teacher', 403, 'Consolidated reports require school reporting access.');
    }

    /**
     * @param  array{scope: 'school'|'district'|'division', scope_id: int, academic_year_id: int, period: int}  $filters
     */
    public function generate(string $id, array $filters): string
    {
        $scope = $filters['scope'];
        $schools = School::query()->whereIn('id', $this->schoolIds($filters))->get()->keyBy('id');
        $districts = District::query()->whereIn('id', $schools->pluck('district_id'))->get()->keyBy('id');
        $rows = [];
        if ($scope !== 'school') {
            foreach ($scope === 'district' ? $schools : $districts as $entity) {
                $rows[$entity->id] = $this->emptyRow($entity->name, (string) ($entity->code ?? ''));
            }
        }

        $classrooms = Classroom::query()
            ->with('section')
            ->whereIn('school_id', $schools->keys())
            ->where('academic_year_id', $filters['academic_year_id'])
            ->whereIn('grade_level_id', DB::table('tbl_grade_levels')->select('id')->whereIn('level', ['Kinder', 'Kindergarten']))
            ->orderBy('id')->lazy(100);
        $references = [ECDC::getScaledScoreAgeBracket_B(), ECDC::getScaledScoreAgeBracket_C()];
        foreach ($classrooms as $classroom) {
            $group = match ($scope) {
                'school' => $classroom->id,
                'district' => $classroom->school_id,
                'division' => $schools[$classroom->school_id]->district_id,
            };
            $rows[$group] ??= $this->emptyRow($classroom->section?->section ?? 'Section '.$classroom->id);
            $this->countClassroom($rows[$group], $classroom, $filters, $references);
        }

        $book = IOFactory::load(public_path('templates/Template-ECDC-Report-'.ucfirst($scope).'.xlsx'));
        $sheet = $book->getActiveSheet();
        if ($scope === 'division') {
            $sheet->removeRow(1, 24);
        }
        $first = $scope === 'school' ? 9 : 8;
        $capacity = $scope === 'division' ? 26 : 20;
        $lastColumn = $scope === 'district' ? 74 : 73;
        $totalRow = $first + $capacity;
        if (count($rows) > $capacity) {
            $extra = count($rows) - $capacity;
            $sheet->insertNewRowBefore($totalRow, $extra);
            for ($r = $totalRow; $r < $totalRow + $extra; $r++) {
                $sheet->duplicateStyle($sheet->getStyle('A'.$first.':'.Coordinate::stringFromColumnIndex($lastColumn).$first), 'A'.$r.':'.Coordinate::stringFromColumnIndex($lastColumn).$r);
            }
            $totalRow += $extra;
        }
        for ($r = $first; $r <= $totalRow; $r++) {
            for ($c = 1; $c <= $lastColumn; $c++) {
                $sheet->setCellValue([$c, $r], null);
            }
        }
        $year = AcademicYear::findOrFail($filters['academic_year_id']);
        $period = ECDC::periodLabel($filters['period']);
        $title = $period.' ECD Assessment '.$year->from.'-'.$year->to;
        $entity = match ($scope) {
            'school' => $schools->first(),
            'district' => District::findOrFail($filters['scope_id']),
            'division' => Division::findOrFail($filters['scope_id']),
        };
        $division = $scope === 'division' ? $entity : Division::find($districts->first()?->division_id);
        $sheet->setCellValueExplicit('A1', 'SCHOOLS DIVISION OFFICE OF '.mb_strtoupper($division?->name ?? ''), DataType::TYPE_STRING);
        if ($scope === 'school') {
            $sheet->setCellValueExplicit('A2', $districts->first()?->name ?? '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('A3', $entity->name, DataType::TYPE_STRING);
        } elseif ($scope === 'district') {
            $sheet->setCellValueExplicit('A2', $entity->name, DataType::TYPE_STRING);
        }
        $sheet->setCellValue('A'.($scope === 'district' ? 4 : ($scope === 'division' ? 4 : 5)), $title);
        $totals = $this->emptyRow('Total');
        foreach (array_values($rows) as $i => $row) {
            $this->writeRow($sheet, $first + $i, $row, $scope, $i + 1);
            foreach (['enrollment', 'counts'] as $key) {
                foreach ($row[$key] as $k => $value) {
                    $totals[$key][$k] += $value;
                }
            }
            $totals['unclassified'] += $row['unclassified'];
        }
        $this->writeRow($sheet, $totalRow, $totals, $scope, null);
        $sheet->getPageSetup()->setPrintArea('A1:'.Coordinate::stringFromColumnIndex($lastColumn).$totalRow);
        $ages = $book->createSheet()->setTitle('Interpretation by age');
        $ages->fromArray(['Group', 'Age group', 'Interpretation', 'Male', 'Female', 'Total', 'Age group total', 'Percentage'], null, 'A1');
        $r = 2;
        foreach ([...array_values($rows), $totals] as $row) {
            for ($age = 0; $age < 3; $age++) {
                $denominator = array_sum(array_slice($row['counts'], $age * 10, 10));
                for ($interpretation = 0; $interpretation < 5; $interpretation++) {
                    $offset = $age * 10 + $interpretation * 2;
                    $male = $row['counts'][$offset];
                    $female = $row['counts'][$offset + 1];
                    $ages->setCellValueExplicit('A'.$r, $row['name'], DataType::TYPE_STRING);
                    $ages->fromArray([self::AGES[$age], self::INTERPRETATIONS[$interpretation], $male, $female, $male + $female, $denominator, $denominator ? ($male + $female) / $denominator : 0], null, 'B'.$r, true);
                    $ages->getStyle('H'.$r)->getNumberFormat()->setFormatCode('0.00%');
                    $r++;
                }
            }
        }
        $ages->freezePane('D2');
        foreach (range('A', 'H') as $column) {
            $ages->getColumnDimension($column)->setAutoSize(true);
        }
        $notes = $book->createSheet()->setTitle('Report notes');
        $notes->fromArray([
            ['Generated at', now()->toDateTimeString()],
            ['Period', $period],
            ['Age', 'Completed years and months at the individual assessment date; classroom date is the fallback.'],
            ['Interpretation by age', 'Interpretation count / assessed learners in that age group. Both sexes are included in the denominator.'],
            ['Main summary', 'Interpretation count / total active Kinder enrollment. Testing coverage uses the same denominator.'],
            ['Unclassified assessed learners', $totals['unclassified']],
            ['Unclassified meaning', 'Missing/invalid gender, birth date, or age outside the template groups. Included in enrollment; excluded from interpretation counts.'],
            ['Missing assessment', 'Included in enrollment; excluded from interpretation counts.'],
        ]);
        $book->setActiveSheetIndex(0);
        $path = 'ecdc-reports/'.$id.'.xlsx';
        $disk = Storage::disk('local');
        $disk->makeDirectory('ecdc-reports');
        try {
            IOFactory::createWriter($book, 'Xlsx')->save($disk->path($path.'.tmp'));
            if (! $disk->move($path.'.tmp', $path)) {
                throw new \RuntimeException('Unable to save ECDC report.');
            }
        } finally {
            $disk->delete($path.'.tmp');
            $book->disconnectWorksheets();
        }

        return $path;
    }

    private function schoolIds(array $filters): array
    {
        return School::query()->when($filters['scope'] === 'school', fn ($q) => $q->whereKey($filters['scope_id']))
            ->when($filters['scope'] === 'district', fn ($q) => $q->where('district_id', $filters['scope_id']))
            ->when($filters['scope'] === 'division', fn ($q) => $q->whereIn('district_id', District::query()->select('id')->where('division_id', $filters['scope_id'])))
            ->pluck('id')->all();
    }

    /**
     * @return array{name: string, code: string, enrollment: list<int>, counts: list<int>, unclassified: int}
     */
    private function emptyRow(string $name, string $code = ''): array
    {
        return ['name' => $name, 'code' => $code, 'enrollment' => [0, 0, 0], 'counts' => array_fill(0, 30, 0), 'unclassified' => 0];
    }

    private function countClassroom(array &$row, Classroom $classroom, array $filters, array $references): void
    {
        $students = StudentClassroom::query()
            ->join('tbl_students as s', 's.id', 'tbl_student_classrooms.student_id')
            ->join('tbl_users as u', 'u.id', 's.user_id')
            ->join('tbl_persons as p', 'p.id', 'u.person_id')
            ->where('classroom_id', $classroom->id)->where('tbl_student_classrooms.status', 1)
            ->whereNull('s.deleted_at')
            ->select('s.id', 'p.gender', 'p.birth_date')->distinct()->get();
        $responses = DB::table('tbl_student_ecdcs as response')
            ->join('tbl_ecdcs as assessment', 'assessment.id', 'response.ecdc_id')
            ->join('tbl_ecdc_competencies as competency', 'competency.id', 'response.ecdc_competency_id')
            ->where('assessment.classroom_id', $classroom->id)
            ->where('assessment.academic_year_id', $filters['academic_year_id'])
            ->where('assessment.period', $filters['period'])
            ->whereIn('response.student_id', $students->pluck('id'))
            ->select('response.id', 'response.student_id', 'response.ecdc_id', 'response.ecdc_competency_id', 'response.score', 'response.date_of_assessment', 'competency.domain_id', 'assessment.date')
            ->orderByDesc('assessment.date')->orderByDesc('assessment.id')->orderByDesc('response.id')->get()->groupBy('student_id');
        foreach ($students as $student) {
            $sex = match (strtoupper(trim((string) $student->gender))) {
                'M', 'MALE' => 0, 'F', 'FEMALE' => 1, default => null
            };
            $row['enrollment'][2]++;
            if ($sex !== null) {
                $row['enrollment'][$sex]++;
            }
            $answers = $responses->get($student->id);
            if (! $answers || $answers->isEmpty()) {
                continue;
            }
            $latest = $answers->first();
            $answers = $answers->where('ecdc_id', $latest->ecdc_id)->unique('ecdc_competency_id');
            $date = $answers->pluck('date_of_assessment')->filter()->first() ?? $latest->date;
            if (! $date || ! $student->birth_date || $sex === null) {
                $row['unclassified']++;

                continue;
            }
            $birth = CarbonImmutable::parse($student->birth_date);
            $tested = CarbonImmutable::parse($date);
            $months = (int) $birth->diffInMonths($tested, false);
            $age = match (true) {
                $months >= 49 && $months <= 60 => 0, $months >= 61 && $months <= 71 => 1, $months >= 72 && $months <= 83 => 2, default => null
            };
            if ($age === null) {
                $row['unclassified']++;

                continue;
            }
            $scaled = 0;
            foreach ($answers->groupBy('domain_id') as $domain => $items) {
                $raw = $items->filter(fn ($answer): bool => (string) $answer->score === '1')->count();
                $reference = $references[$age === 0 ? 0 : 1][$domain] ?? [];
                if ($reference !== []) {
                    $scaled += $reference[min(max($raw, min(array_keys($reference))), max(array_keys($reference)))];
                }
            }
            $score = ECDC::getStandardScore($scaled);
            $interpretation = match (true) {
                $score <= 69 => 0, $score <= 79 => 1, $score <= 119 => 2, $score <= 129 => 3, default => 4
            };
            $row['counts'][$age * 10 + $interpretation * 2 + $sex]++;
        }
    }

    private function writeRow(Worksheet $sheet, int $r, array $row, string $scope, ?int $number): void
    {
        $shift = $scope === 'district' ? 1 : 0;
        $sheet->setCellValue('A'.$r, $number ?? 'Total');
        $sheet->setCellValueExplicit('B'.$r, $scope === 'district' ? $row['code'] : ($number ? $row['name'] : ''), DataType::TYPE_STRING);
        if ($shift) {
            $sheet->setCellValueExplicit('C'.$r, $number ? $row['name'] : '', DataType::TYPE_STRING);
        }
        $sheet->fromArray($row['enrollment'], null, Coordinate::stringFromColumnIndex(3 + $shift).$r, true);
        for ($age = 0; $age < 3; $age++) {
            for ($i = 0; $i < 5; $i++) {
                $offset = $age * 10 + $i * 2;
                $male = $row['counts'][$offset];
                $female = $row['counts'][$offset + 1];
                $sheet->fromArray([$male, $female, $male + $female], null, Coordinate::stringFromColumnIndex(6 + $shift + $age * 15 + $i * 3).$r, true);
            }
        }
        $tested = array_sum($row['counts']);
        $sheet->setCellValue([52 + $shift, $r], $tested);
        for ($i = 0; $i < 5; $i++) {
            $male = $female = 0;
            for ($age = 0; $age < 3; $age++) {
                $male += $row['counts'][$age * 10 + $i * 2];
                $female += $row['counts'][$age * 10 + $i * 2 + 1];
            }
            $column = 53 + $shift + $i * 4;
            $percentage = $row['enrollment'][2] ? ($male + $female) / $row['enrollment'][2] : 0;
            $sheet->fromArray([$male, $female, $male + $female, $percentage], null, Coordinate::stringFromColumnIndex($column).$r, true);
            $sheet->getStyle([$column + 3, $r])->getNumberFormat()->setFormatCode('0.00%');
        }
        $sheet->setCellValue([73 + $shift, $r], $row['enrollment'][2] ? $tested / $row['enrollment'][2] : 0);
        $sheet->getStyle([73 + $shift, $r])->getNumberFormat()->setFormatCode('0.00%');
    }
}
