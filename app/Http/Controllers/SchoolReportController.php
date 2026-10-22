<?php

namespace App\Http\Controllers;

use App\Models\AssessmentType;
use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\Semester;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\Track;
use App\Models\User;
use App\Support\RoleDataScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class SchoolReportController extends Controller
{
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
        $gradeLevels = GradeLevel::query()->orderBy('level')->get();
        $periods = Period::query()->orderBy('id')->get();
        $assessmentTypes = AssessmentType::query()->orderBy('type')->get();
        $subjects = Subject::query()->orderBy('title')->get();
        $tracks = Track::query()->orderBy('name')->get();
        $strands = Strand::query()->orderBy('name')->get();
        $semesters = Semester::query()->orderBy('id')->get();

        return view('reports.school-first', compact(
            'page',
            'schools',
            'gradeLevels',
            'periods',
            'assessmentTypes',
            'subjects',
            'tracks',
            'strands',
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

        $validated = $request->validate([
            'school_ids' => ['required', 'array', 'min:1'],
            'school_ids.*' => ['required', 'integer', 'distinct', 'exists:tbl_schools,id'],
            'education_level_id' => ['required', 'integer', 'in:1,2,3'],
            'period_id' => ['required', 'integer', 'exists:tbl_periods,id'],
            'grade_level_id' => ['required', 'integer', 'exists:tbl_grade_levels,id'],
            'subject_id' => [
                Rule::requiredIf($request->integer('grade_level_id') < 13),
                'nullable',
                'integer',
                'exists:tbl_subjects,id',
            ],
            'assessment_type_id' => ['required', 'integer', 'exists:tbl_assessment_types,id'],
            'assessment_date' => ['required', 'date_format:Y-m-d'],
            'report_type_id' => ['required', 'in:LC,AL,SA'],
            'track_id' => ['nullable', 'integer', 'exists:tbl_tracks,id'],
            'strand_id' => ['nullable', 'integer', 'exists:tbl_strands,id'],
            'semester_id' => ['nullable', 'integer', 'exists:tbl_semesters,id'],
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
