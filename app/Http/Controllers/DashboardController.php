<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CustomFunction;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Response;

class DashboardController extends Controller
{
    public function index()
    {

        $page = [
            'name' => 'Dashboard',
            'title' => 'Dashboard',
            'crumb' => ['Dashboard' => '/dashboard'],
        ];

        $academic_year = AcademicYear::where('is_active', 1)->first();
        $details = CustomFunction::getUserDetails(Auth::user()->id);
        $schools = School::count();
        $users = User::where('classification', '<>', 'Student')->count();
        $students = Student::count();
        $teachers = Teacher::count();
        $classrooms = Classroom::query()
            ->where('academic_year_id', $academic_year?->id)
            ->count();
        $sections = Section::count();
        $dashboard_teachers = User::query()
            ->select('tbl_teachers.id', 'username', 'first_name', 'middle_name', 'last_name', 'suffix')
            ->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('classification', 'Teacher')
            ->orderBy('last_name')
            ->get();
        $dashboard_students = Student::query()
            ->select('tbl_students.id', 'lrn', 'first_name', 'middle_name', 'last_name', 'suffix')
            ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->orderBy('last_name')
            ->get();
        $dashboard_classrooms = Classroom::query()
            ->select('tbl_classrooms.id', 'tbl_sections.section', 'tbl_grade_levels.level')
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->where('tbl_classrooms.academic_year_id', $academic_year?->id)
            ->orderBy('level')
            ->orderBy('section')
            ->get();
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

        return view('layouts.dashboard', compact('page', 'academic_year', 'details',
            'users', 'schools', 'students', 'teachers', 'classrooms', 'sections',
            'dashboard_teachers', 'dashboard_students', 'dashboard_classrooms', 'daily_quote',
            'templates', 'classification'
        ));
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
