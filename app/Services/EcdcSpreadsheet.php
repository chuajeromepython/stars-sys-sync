<?php

namespace App\Services;

use App\Models\CustomFunction;
use App\Models\ECDC;
use App\Models\ECDCCompetency;
use App\Models\ECDCDomain;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EcdcSpreadsheet
{
    public function template(int $classroomId): Spreadsheet
    {
        $book = IOFactory::load(public_path('templates/Template-ECDC-Uploader.xlsx'));
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Encode here');
        $sheet->setAutoFilter('');
        $sheet->setCellValue('C2', 'Beginning of School Year');
        $sheet->getDataValidation('C2')->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_STOP)->setAllowBlank(false)
            ->setShowDropDown(true)->setShowErrorMessage(true)
            ->setErrorTitle('Invalid assessment period')
            ->setError('Select Beginning of School Year or Ending of School Year.')
            ->setFormula1('"Beginning of School Year,Ending of School Year"');
        $sheet->removeRow(4, max(1, $sheet->getHighestRow() - 3));
        $lastColumn = max(4, Coordinate::columnIndexFromString($sheet->getHighestColumn()));
        for ($column = 4; $column <= $lastColumn; $column++) {
            for ($row = 1; $row <= 3; $row++) {
                $sheet->setCellValue([$column, $row], null);
            }
        }
        $sheet->setCellValue('A3', 'Assessment date');

        $students = CustomFunction::getStudentsPerClassroom($classroomId);
        $headingEnd = Coordinate::stringFromColumnIndex(max(3, 3 + $students->count() * 3));
        $colors = [
            'red' => 'FFDC3545', 'orange' => 'FFFF851B', 'yellow' => 'FFFFC107',
            'green' => 'FF28A745', 'primary' => 'FF007BFF', 'info' => 'FF17A2B8', 'purple' => 'FF605CA8',
        ];
        $row = 4;
        foreach (ECDCDomain::query()->with(['competencies' => fn ($query) => $query->orderBy('id')])->orderBy('id')->get() as $domainIndex => $domain) {
            $sheet->setCellValue('A'.$row, '*');
            $sheet->setCellValueExplicit('B'.$row, $domain->domain, DataType::TYPE_STRING);
            $sheet->mergeCells('B'.$row.':C'.$row);
            $color = ECDC::domainColor($domainIndex);
            $heading = $sheet->getStyle('A'.$row.':'.$headingEnd.$row);
            $heading->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($colors[$color]);
            $heading->getFont()->setBold(true)->getColor()->setARGB(in_array($color, ['red', 'primary', 'purple'], true) ? 'FFFFFFFF' : 'FF212529');
            $row++;
            foreach ($domain->competencies as $index => $competency) {
                $sheet->setCellValue('A'.$row, $competency->id);
                $sheet->setCellValue('B'.$row, $index + 1);
                $sheet->setCellValueExplicit('C'.$row, $competency->competency, DataType::TYPE_STRING);
                $sheet->getStyle('C'.$row)->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($row)->setRowHeight(45);
                $row++;
            }
        }
        foreach ($students as $index => $student) {
            $column = $index * 3 + 4;
            $first = Coordinate::stringFromColumnIndex($column);
            $last = Coordinate::stringFromColumnIndex($column + 2);
            for ($header = 1; $header <= 3; $header++) {
                $range = $first.$header.':'.$last.$header;
                if (! in_array($range, $sheet->getMergeCells(), true)) {
                    $sheet->mergeCells($range);
                }
            }
            $sheet->setCellValueExplicit([$column, 1], (string) $student->lrn, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit([$column, 2], trim($student->last_name.', '.$student->first_name.' '.$student->middle_name), DataType::TYPE_STRING);
            $sheet->getStyle([$column, 3])->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            for ($offset = 0; $offset < 3; $offset++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column + $offset))->setWidth(10);
            }
            for ($r = 4; $r < $row; $r++) {
                if ($sheet->getCell('A'.$r)->getValue() === '*') {
                    foreach (['P', 'O', 'R'] as $offset => $type) {
                        $sheet->setCellValue([$column + $offset, $r], $type);
                    }
                }
            }
        }
        $sheet->getStyle('A1:'.$headingEnd.($row - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF808080');
        $sheet->freezePane('D4');
        $sheet->getColumnDimension('C')->setWidth(80);
        $instructions = $book->createSheet()->setTitle('Instructions');
        $instructions->fromArray([
            ['Row 1', 'Student LRN. Do not change.'],
            ['Row 2', 'Student name.'],
            ['Row 3', 'Individual assessment date in each student group, starting D3, G3, J3. Use yyyy-mm-dd or an Excel date.'],
            ['P / O / R columns', 'Enter 1 in exactly one column for each present competency.'],
            ['Score', 'A marked column saves score 1 and its P, O, or R presentation type.'],
            ['Unmarked competency', 'No 1 in P, O, or R saves score - and a null presentation type.'],
            ['Blank / - / 0 / *', 'No present mark.'],
            ['Untested student', 'A student with no 1 anywhere in their P/O/R group is skipped.'],
            ['Competency rows', 'Do not change competency IDs. Download a fresh template after the competency library changes.'],
        ]);
        $instructions->getColumnDimension('A')->setWidth(24);
        $instructions->getColumnDimension('B')->setWidth(100);
        $instructions->getStyle('A1:B9')->getAlignment()->setWrapText(true);
        $book->setActiveSheetIndex(0);

        return $book;
    }

    /**
     * @return array<int, array{date: string, responses: array<int, array{score: string, present_type: ?string}>}>
     */
    public function parse(Spreadsheet $book, int $classroomId): array
    {
        $sheet = collect($book->getAllSheets())->first(fn ($sheet): bool => strtolower($sheet->getTitle()) === 'encode here');
        if ($sheet === null) {
            throw ValidationException::withMessages(['file' => 'The workbook must contain an Encode here worksheet.']);
        }
        if (! in_array(trim((string) $sheet->getCell('C2')->getValue()), ['Beginning of School Year', 'Ending of School Year'], true)) {
            throw ValidationException::withMessages(['file' => 'C2 must contain Beginning of School Year or Ending of School Year.']);
        }
        $available = ECDCCompetency::query()->pluck('id')->mapWithKeys(fn ($id): array => [(int) $id => true])->all();
        $competencyRows = [];
        $errors = [];
        for ($row = 4; $row <= $sheet->getHighestDataRow(); $row++) {
            $id = trim((string) $sheet->getCell('A'.$row)->getValue());
            if ($id === '' || $id === '*' || strtoupper($id) === 'ID') {
                continue;
            }
            if (! ctype_digit($id) || ! isset($available[(int) $id]) || isset($competencyRows[(int) $id])) {
                $errors[] = 'Invalid or outdated competency ID at A'.$row.'. Download a fresh template.';

                continue;
            }
            $competencyRows[(int) $id] = $row;
        }
        if ($competencyRows === []) {
            $errors[] = 'The workbook contains no current ECDC competencies.';
        }
        $students = CustomFunction::getStudentsPerClassroom($classroomId)->keyBy(fn ($student): string => (string) $student->lrn);
        $results = [];
        $seen = [];
        $lastColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        for ($column = 4; $column <= $lastColumn; $column += 3) {
            $letter = Coordinate::stringFromColumnIndex($column);
            $lrn = trim((string) $sheet->getCell([$column, 1])->getValue());
            if ($lrn === '') {
                continue;
            }
            if (! $students->has($lrn) || isset($seen[$lrn])) {
                $errors[] = 'LRN '.$lrn.' at '.$letter.'1 is duplicated or does not belong to this classroom.';

                continue;
            }
            $seen[$lrn] = true;
            $student = $students[$lrn];
            $label = trim($student->last_name.', '.$student->first_name.' '.$student->middle_name).' (LRN '.$lrn.')';
            $responses = [];
            $tested = false;
            foreach ($competencyRows as $id => $row) {
                $types = [];
                foreach (['P', 'O', 'R'] as $offset => $type) {
                    $coordinate = Coordinate::stringFromColumnIndex($column + $offset).$row;
                    $value = trim((string) $sheet->getCell($coordinate)->getValue());
                    if (! in_array($value, ['', '1', '-', '*', '0'], true)) {
                        $errors[] = $label.': invalid value at '.$coordinate.'. Enter 1 in the appropriate P, O, or R column.';
                    }
                    if ($value === '1') {
                        $types[] = $type;
                    }
                }
                if (count($types) > 1) {
                    $end = Coordinate::stringFromColumnIndex($column + 2).$row;
                    $errors[] = $label.': multiple P/O/R marks ('.implode(', ', $types).') at '.$letter.$row.':'.$end.'. Mark only one presentation type per competency.';
                }
                $present = $types !== [];
                $tested = $tested || $present;
                $responses[$id] = ['score' => $present ? '1' : '-', 'present_type' => $types[0] ?? null];
            }
            $dateValue = $sheet->getCell([$column, 3])->getValue();
            if (! $tested && ($dateValue === null || trim((string) $dateValue) === '')) {
                continue;
            }
            try {
                if (is_numeric($dateValue) && (float) $dateValue > 0 && (float) $dateValue < 2958466) {
                    $date = Date::excelToDateTimeObject((float) $dateValue)->format('Y-m-d');
                } else {
                    $raw = trim((string) $dateValue);
                    $parsed = Carbon::createFromFormat('!Y-m-d', $raw);
                    if (! $parsed || $parsed->format('Y-m-d') !== $raw) {
                        throw new \InvalidArgumentException;
                    }
                    $date = $parsed->toDateString();
                }
            } catch (\Throwable) {
                $errors[] = $label.': enter a valid assessment date in '.$letter.'3 (yyyy-mm-dd or an Excel date).';

                continue;
            }
            if (! $tested) {
                continue;
            }
            foreach (array_keys($available) as $id) {
                $responses[$id] ??= ['score' => '-', 'present_type' => null];
            }
            $results[(int) $students[$lrn]->id] = ['date' => $date, 'responses' => $responses];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => $errors]);
        }

        return $results;
    }
}
