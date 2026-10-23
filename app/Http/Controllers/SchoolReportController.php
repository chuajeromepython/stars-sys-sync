<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\AcademicYear;
use App\Models\AssessmentType;
use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\Semester;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\Track;
use App\Models\User;
use App\Support\RoleDataScope;
use App\Support\UserHierarchy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class SchoolReportController extends Controller
{
    private function gradeLabels(int $educationLevel): array
    {
        [$first, $last] = match ($educationLevel) {
            1 => [1, 6], 2 => [7, 10], 3 => [11, 12], default => [1, 12],
        };

        return array_map(static fn (int $grade): string => 'Grade '.$grade, range($first, $last));
    }

    public function index(Request $request, RoleDataScope $roleDataScope): View
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->can('reports.view'), 403);

        $page = [
            'name' => 'Report',
            'title' => 'Report Management',
            'crumb' => ['Reports' => '/reports'],
        ];
        $schools = $roleDataScope->schoolsFor($user)
            ->orderBy('name')
            ->get(['id', 'name']);
        $gradeLevels = GradeLevel::query()->whereIn('level', $this->gradeLabels(0))->get()
            ->sortBy(static fn (GradeLevel $grade): int => (int) str_replace('Grade ', '', $grade->level))->values();
        $periods = Period::query()->orderBy('id')->get();
        $assessmentTypes = AssessmentType::query()->whereRaw('UPPER(TRIM(type)) NOT IN (?, ?, ?, ?)', ['ECD', 'ECDC', 'KINDER', 'KINDERGARTEN'])->orderBy('type')->get();
        $subjects = Subject::query()->whereRaw('UPPER(TRIM(title)) NOT IN (?, ?, ?, ?)', ['ECD', 'ECDC', 'KINDER', 'KINDERGARTEN'])->orderBy('title')->get();
        $tracks = Track::query()->orderBy('name')->get();
        $strands = Strand::query()->orderBy('name')->get();
        $semesters = Semester::query()->orderBy('id')->get();
        $ecdcYears = AcademicYear::query()->orderByDesc('from')->get();
        $ecdcHierarchy = app(UserHierarchy::class)->forUser($user);
        $canSelectReportSchools = in_array($ecdcHierarchy['scope'], ['system', 'division', 'district'], true);
        $canViewEcdcReports = Role::fromClassification($user->classification) !== Role::Teacher;
        $ecdcDistricts = in_array($ecdcHierarchy['scope'], ['system', 'division', 'district'], true)
            ? $roleDataScope->districtsFor($user)->orderBy('name')->get(['id', 'name']) : collect();
        $ecdcDivisions = in_array($ecdcHierarchy['scope'], ['system', 'division'], true)
            ? $roleDataScope->divisionsFor($user)->orderBy('name')->get(['id', 'name']) : collect();

        return view('reports.school-first', compact(
            'page',
            'schools',
            'gradeLevels',
            'periods',
            'assessmentTypes',
            'subjects',
            'tracks',
            'strands',
            'ecdcYears', 'ecdcDistricts', 'ecdcDivisions', 'ecdcHierarchy', 'canSelectReportSchools', 'canViewEcdcReports',
            'semesters'
        ));
    }

    public function generate(
        Request $request,
        RoleDataScope $roleDataScope,
        ReportController $reportController,
    ): JsonResponse {
        $user = $request->user();
        abort_unless($user instanceof User && $user->can('reports.generate'), 403);

        $hierarchy = app(UserHierarchy::class)->forUser($user);
        if (! in_array($hierarchy['scope'], ['system', 'division', 'district'], true)) {
            abort_unless($hierarchy['school_id'] !== null && $roleDataScope->allowsSchool($user, (int) $hierarchy['school_id']), 403, 'No school is assigned to this account.');
            $request->merge(['school_ids' => [(int) $hierarchy['school_id']]]);
        }

        $validated = $request->validate([
            'school_ids' => ['required', 'array', 'min:1'],
            'school_ids.*' => ['required', 'integer', 'distinct', 'exists:tbl_schools,id'],
            'education_level_id' => ['required', 'integer', 'in:1,2,3'],
            'period_id' => ['required', 'integer', 'exists:tbl_periods,id'],
            'grade_level_id' => ['required', 'integer', Rule::exists('tbl_grade_levels', 'id')->whereIn('level', $this->gradeLabels($request->integer('education_level_id')))],
            'subject_id' => [
                'required',
                'nullable',
                'integer',
                Rule::exists('tbl_subjects', 'id')->where(fn ($query) => $query->whereRaw('UPPER(TRIM(title)) NOT IN (?, ?, ?, ?)', ['ECD', 'ECDC', 'KINDER', 'KINDERGARTEN'])),
            ],
            'assessment_type_id' => ['required', 'integer', Rule::exists('tbl_assessment_types', 'id')->where(fn ($query) => $query->whereRaw('UPPER(TRIM(type)) NOT IN (?, ?, ?, ?)', ['ECD', 'ECDC', 'KINDER', 'KINDERGARTEN']))],
            'assessment_date' => ['required', 'date_format:Y-m-d'],
            'report_type_id' => ['required', 'in:LC,AL,SA'],
            'track_id' => ['exclude_unless:education_level_id,3', 'nullable', 'integer', 'exists:tbl_tracks,id'],
            'strand_id' => ['exclude_unless:education_level_id,3', 'nullable', 'integer', 'exists:tbl_strands,id'],
            'semester_id' => ['exclude_unless:education_level_id,3', 'nullable', 'integer', 'exists:tbl_semesters,id'],
        ]);

        $schoolIds = array_map('intval', $validated['school_ids']);
        $schools = $roleDataScope->schoolsFor($user)
            ->whereIn('tbl_schools.id', $schoolIds)
            ->orderBy('name')
            ->get(['tbl_schools.id', 'tbl_schools.name', 'tbl_schools.district_id']);

        if ($schools->count() !== count($schoolIds)) {
            abort(404);
        }

        $reportInputs = collect($validated)->except('school_ids')->all();
        $reports = [];

        foreach ($schools as $school) {
            $schoolRequest = Request::create('/reports/generate', 'POST', $reportInputs + [
                'district_id' => $school->district_id,
                'school_id' => $school->id,
            ]);
            $schoolRequest->setUserResolver(static fn (): User => $user);

            $reports[] = [
                'school_id' => (int) $school->id,
                'school_name' => $school->name,
                'report_type_id' => $validated['report_type_id'],
                'report' => $reportController->generate($schoolRequest),
            ];
        }

        return response()->json(['reports' => $reports]);
    }
}
