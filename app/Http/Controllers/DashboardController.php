<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CustomFunction;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\DataTable\DashboardDataTable;
use App\Services\DataTable\DataTablePaginator;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Response;

class DashboardController extends Controller
{
    public function index(DashboardDataTable $dashboardDataTable)
    {

        $page = [
            'name' => 'Dashboard',
            'title' => 'Dashboard',
            'crumb' => ['Dashboard' => '/dashboard'],
        ];

        $academic_year = AcademicYear::where('is_active', 1)->first();
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

        $statistics = $this->statistics($academic_year);
        $recordTypes = $dashboardDataTable->typesFor(Auth::user());

        return view('layouts.dashboard', compact('page', 'academic_year', 'details',
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
    private function statistics(?AcademicYear $academicYear): array
    {
        $scope = app(DashboardDataTable::class)->scope(Auth::user());
        $yearId = $academicYear?->id;

        if ($scope['teacher_id'] !== null) {
            $assigned = DB::table('tbl_teacher_classes')
                ->where('teacher_id', $scope['teacher_id'])
                ->distinct()
                ->pluck('classroom_id');

            $studentsInAssigned = DB::table('tbl_student_classrooms')
                ->whereIn('classroom_id', $assigned)
                ->where('status', 1)
                ->distinct()
                ->count('student_id');

            return [
                ['label' => 'Advisory', 'value' => $this->teacherClassCount($scope['teacher_id'], true), 'icon' => 'fas fa-user-check', 'url' => '/classrooms'],
                ['label' => 'Subject Class', 'value' => $this->teacherClassCount($scope['teacher_id'], false), 'icon' => 'fas fa-chalkboard', 'url' => '/classrooms'],
                ['label' => 'Students', 'value' => (int) $studentsInAssigned, 'icon' => 'fas fa-graduation-cap', 'url' => '/students'],
                ['label' => 'Classrooms', 'value' => $assigned->count(), 'icon' => 'fas fa-door-open', 'url' => '/classrooms'],
            ];
        }

        if ($scope['school_id'] !== null) {
            $schoolId = $scope['school_id'];

            return [
                ['label' => 'Classrooms', 'value' => Classroom::where('school_id', $schoolId)->where('academic_year_id', $yearId)->count(), 'icon' => 'fas fa-chalkboard-teacher', 'url' => '/classrooms'],
                ['label' => 'Teachers', 'value' => Teacher::where('school_id', $schoolId)->count(), 'icon' => 'fas fa-user-tie', 'url' => '/teachers'],
                ['label' => 'Students', 'value' => Student::where('school_id', $schoolId)->count(), 'icon' => 'fas fa-graduation-cap', 'url' => '/students'],
                ['label' => 'Sections', 'value' => Section::where('school_id', $schoolId)->count(), 'icon' => 'fas fa-layer-group', 'url' => '/sections'],
            ];
        }

        return [
            ['label' => 'Classrooms', 'value' => Classroom::where('academic_year_id', $yearId)->count(), 'icon' => 'fas fa-chalkboard-teacher', 'url' => '/classrooms'],
            ['label' => 'Teachers', 'value' => Teacher::count(), 'icon' => 'fas fa-user-tie', 'url' => '/teachers'],
            ['label' => 'Students', 'value' => Student::count(), 'icon' => 'fas fa-graduation-cap', 'url' => '/students'],
            ['label' => 'Sections', 'value' => Section::count(), 'icon' => 'fas fa-layer-group', 'url' => '/sections'],
        ];
    }

    /**
     * How many of a teacher's classes are advisory, or are subject classes.
     */
    private function teacherClassCount(int $teacherId, bool $advisory): int
    {
        return DB::table('tbl_teacher_classes')
            ->where('teacher_id', $teacherId)
            ->where('advisory', $advisory ? 1 : 0)
            ->distinct()
            ->count('classroom_id');
    }

    /**
     * Server-side processed records for the dashboard school directory table.
     */
    public function records(Request $request, DashboardDataTable $dashboardDataTable, DataTablePaginator $paginator): JsonResponse
    {
        $type = (string) $request->query('type', '');

        // The endpoint enforces the same rule as the filter select on the page:
        // a record type the role is not offered resolves to nothing, so a
        // hand crafted query string cannot widen the directory.
        if (! $dashboardDataTable->allows($type)) {
            $type = '';
        }

        $resolved = $dashboardDataTable->resolve($type);

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
