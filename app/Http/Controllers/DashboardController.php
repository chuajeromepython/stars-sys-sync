<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CustomFunction;
use App\Models\Teacher;
use App\Services\DataTable\DashboardDataTable;
use App\Services\DataTable\DataTablePaginator;
use App\Support\UserHierarchy;
use Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Response;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardDataTable $dashboardDataTable, UserHierarchy $userHierarchy)
    {

        $page = [
            'name' => 'Dashboard',
            'title' => 'Dashboard',
            'crumb' => ['Dashboard' => '/dashboard'],
        ];

        $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:tbl_academic_years,id'],
        ]);

        $academicYears = AcademicYear::query()->orderByDesc('from')->orderByDesc('id')->get();
        $academic_year = $request->filled('academic_year_id')
            ? AcademicYear::query()->find($request->integer('academic_year_id'))
            : AcademicYear::active();
        $details = CustomFunction::getUserDetails(Auth::user()->id);
        $quotes = [
            'Education is the most powerful weapon which you can use to change the world.',
            'The beautiful thing about learning is that no one can take it away from you.',
            'Success is the sum of small efforts repeated day in and day out.',
            'A teacher affects eternity; he can never tell where his influence stops.',
            'The best way to predict the future is to create it.',
        ];
        $daily_quote = $quotes[now()->dayOfYear % count($quotes)];
        $templates = $this->templates();
        $classification = Auth::user()->classification;

        $statistics = $this->statistics($academic_year, $userHierarchy);
        $recordTypes = $dashboardDataTable->typesFor(Auth::user());

        return view('layouts.dashboard', compact('page', 'academic_year', 'academicYears', 'details',
            'statistics', 'recordTypes', 'daily_quote', 'templates', 'classification'
        ));
    }

    /**
     * The stat cards shown on the dashboard, scoped to what the role is
     * responsible for.
     *
     * A teacher sees the classrooms and students they are actually assigned to,
     * split by advisory and subject class, because the whole school roster is
     * not their responsibility and reporting it as such is misleading. A school
     * head and a department head see their own school. Division office roles are
     * not scoped to a school and keep the division wide counts.
     *
     * @return list<array{label: string, value: int, icon: string, url: string}>
     */
    private function statistics(?AcademicYear $academicYear, UserHierarchy $userHierarchy): array
    {
        if ($academicYear === null) {
            return $this->emptyStatistics();
        }

        $hierarchy = $userHierarchy->forUser(Auth::user());
        $classrooms = $this->classroomsFor($academicYear->id, $hierarchy);

        if ($hierarchy['scope'] === 'teacher') {
            $teacherId = $hierarchy['teacher_id'];

            return [
                ['label' => 'Advisory Students', 'value' => $this->teacherStudentCount($teacherId, $academicYear->id, true), 'icon' => 'fas fa-user-check', 'url' => '/students'],
                ['label' => 'Subject Class Students', 'value' => $this->teacherStudentCount($teacherId, $academicYear->id, false), 'icon' => 'fas fa-chalkboard', 'url' => '/students'],
                ['label' => 'Students', 'value' => $this->studentCount($classrooms), 'icon' => 'fas fa-graduation-cap', 'url' => '/students'],
                ['label' => 'Classrooms', 'value' => $this->classroomCount($classrooms), 'icon' => 'fas fa-door-open', 'url' => '/classrooms'],
            ];
        }

        return [
            ['label' => 'Classrooms', 'value' => $this->classroomCount($classrooms), 'icon' => 'fas fa-chalkboard-teacher', 'url' => '/classrooms'],
            ['label' => 'Teachers', 'value' => $this->teacherCount($classrooms), 'icon' => 'fas fa-user-tie', 'url' => '/teachers'],
            ['label' => 'Students', 'value' => $this->studentCount($classrooms), 'icon' => 'fas fa-graduation-cap', 'url' => '/students'],
            ['label' => 'Sections', 'value' => $this->sectionCount($classrooms), 'icon' => 'fas fa-layer-group', 'url' => '/sections'],
        ];
    }

    /**
     * @param  array{scope: string, teacher_id: int|null, school_id: int|null, district_id: int|null, division_id: int|null}  $hierarchy
     */
    private function classroomsFor(int $academicYearId, array $hierarchy): Builder
    {
        $query = DB::table('tbl_classrooms')
            ->join('tbl_schools', 'tbl_classrooms.school_id', 'tbl_schools.id')
            ->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
            ->whereNull('tbl_classrooms.deleted_at')
            ->where('tbl_classrooms.academic_year_id', $academicYearId)
            ->select('tbl_classrooms.id');

        match ($hierarchy['scope']) {
            'teacher' => $query->join('tbl_teacher_classes', 'tbl_classrooms.id', 'tbl_teacher_classes.classroom_id')
                ->where('tbl_teacher_classes.teacher_id', $hierarchy['teacher_id']),
            'school' => $query->where('tbl_classrooms.school_id', $hierarchy['school_id']),
            'district' => $query->where('tbl_schools.district_id', $hierarchy['district_id']),
            'division' => $query->where('tbl_districts.division_id', $hierarchy['division_id']),
            'none' => $query->whereRaw('1 = 0'),
            default => null,
        };

        return $query->distinct();
    }

    private function teacherStudentCount(int $teacherId, int $academicYearId, bool $advisory): int
    {
        return DB::table('tbl_student_classrooms')
            ->join('tbl_classrooms', 'tbl_student_classrooms.classroom_id', 'tbl_classrooms.id')
            ->join('tbl_teacher_classes', 'tbl_classrooms.id', 'tbl_teacher_classes.classroom_id')
            ->whereNull('tbl_student_classrooms.deleted_at')
            ->whereNull('tbl_classrooms.deleted_at')
            ->where('tbl_student_classrooms.status', 1)
            ->where('tbl_classrooms.academic_year_id', $academicYearId)
            ->where('tbl_teacher_classes.teacher_id', $teacherId)
            ->where('tbl_teacher_classes.advisory', $advisory ? 1 : 0)
            ->distinct()
            ->count('tbl_student_classrooms.student_id');
    }

    private function classroomCount(Builder $classrooms): int
    {
        return DB::query()->fromSub(clone $classrooms, 'scoped_classrooms')->count();
    }

    private function studentCount(Builder $classrooms): int
    {
        return DB::table('tbl_student_classrooms')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->whereIn('classroom_id', clone $classrooms)
            ->distinct()
            ->count('student_id');
    }

    private function teacherCount(Builder $classrooms): int
    {
        return DB::table('tbl_teacher_classes')
            ->whereIn('classroom_id', clone $classrooms)
            ->distinct()
            ->count('teacher_id');
    }

    private function sectionCount(Builder $classrooms): int
    {
        return DB::table('tbl_classrooms')
            ->whereIn('id', clone $classrooms)
            ->distinct()
            ->count('section_id');
    }

    /**
     * @return list<array{label: string, value: int, icon: string, url: string}>
     */
    private function emptyStatistics(): array
    {
        return [
            ['label' => 'Classrooms', 'value' => 0, 'icon' => 'fas fa-chalkboard-teacher', 'url' => '/classrooms'],
            ['label' => 'Teachers', 'value' => 0, 'icon' => 'fas fa-user-tie', 'url' => '/teachers'],
            ['label' => 'Students', 'value' => 0, 'icon' => 'fas fa-graduation-cap', 'url' => '/students'],
            ['label' => 'Sections', 'value' => 0, 'icon' => 'fas fa-layer-group', 'url' => '/sections'],
        ];
    }

    /**
     * Server-side processed records for the dashboard school directory table.
     */
    public function records(Request $request, DashboardDataTable $dashboardDataTable, DataTablePaginator $paginator): JsonResponse
    {
        $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:tbl_academic_years,id'],
        ]);

        $type = (string) $request->query('type', '');
        $academicYearId = $request->filled('academic_year_id')
            ? $request->integer('academic_year_id')
            : AcademicYear::active()?->id;

        // The endpoint enforces the same rule as the filter select on the page:
        // a record type the role is not offered resolves to nothing, so a
        // hand crafted query string cannot widen the directory.
        if (! $dashboardDataTable->allows($type)) {
            $type = '';
        }

        $resolved = $dashboardDataTable->resolve($type, null, $academicYearId);

        if ($resolved === null) {
            return response()->json([
                'draw' => (int) $request->query('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        return response()->json($paginator->paginate($resolved['query'], $request, $resolved['columns']));
    }

    public function database()
    {

        $lists = DB::select('SHOW TABLES');
        foreach ($lists as $key => $list) {
            $tables[$list->Tables_in_db_stars_2022] = Schema::getColumnListing($list->Tables_in_db_stars_2022);
        }

        return view('db', compact('tables'));
    }

    public function truncate($key)
    {

        if ($key == 'parasabayan2022') {
            CustomFunction::truncate();

            return redirect('/dashboard')->with('success', 'Table Successfully truncated!');
        } else {

            return redirect('/forbidden');
        }

    }

    public function download_template($code)
    {

        $templates = $this->templates();
        $file = public_path('uploaders/'.$templates[$code].'.xlsx');

        return Response::download($file);
    }

    public function templates()
    {
        $templates = [
            'ACU' => 'ADVISORY-CLASS-UPLOADER',
            'ACU-SHS' => 'ADVISORY-CLASS-UPLOADER-SHS-NEW',
            'CU' => 'COMPETENCY-UPLOADER',
            'SHU' => 'SCHOOL-HEAD-UPLOADER',
            'SCU' => 'SUBJECT-CLASS-UPLOADER-2',
            'SU' => 'SECTION-UPLOADER',
            'TAU' => 'TEACHER-ACCOUNT-UPLOADER',
            'ANS-KEY' => 'ANSWER-KEY-UPLOADER',
        ];

        return $templates;
    }
}
