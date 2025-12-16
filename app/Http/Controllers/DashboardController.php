<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Auth;
use File;
use Response;

use App\Models\CustomFunction;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {

        $page = [
            'name'      =>  'Dashboard',
            'title'     =>  'Dashboard',
            'crumb'     =>  array('Dashboard' => '/dashboard')
        ];

        $academic_year = AcademicYear::where('is_active', 1)->first();
        $details = CustomFunction::getUserDetails(Auth::user()->id);
        $classification = Auth::user()->classification;
        $templates = $this->templates();
        
        // Get statistics based on user classification
        $statistics = $this->getStatistics($classification);
        
        // Get quick links based on user classification
        $quickLinks = $this->getQuickLinks($classification);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($classification);
        
        // Get reports based on user classification
        $reports = $this->getReports($classification);
        
        return view('layouts.dashboard', compact(
            'page', 'academic_year', 'details', 'classification', 
            'templates', 'statistics', 'quickLinks', 'recentActivities', 'reports'
        ));
    }

    private function getStatistics($classification)
    {
        $stats = [];
        
        switch ($classification) {
            case 'System Administrator':
                $stats = [
                    ['title' => 'Total Divisions', 'value' => DB::table('tbl_divisions')->count(), 'icon' => 'fa-map', 'color' => 'primary', 'link' => '/divisions'],
                    ['title' => 'Total Schools', 'value' => School::count(), 'icon' => 'fa-school', 'color' => 'success', 'link' => '/schools'],
                    ['title' => 'Total Users', 'value' => User::where('classification', '<>', 'Student')->count(), 'icon' => 'fa-users', 'color' => 'info', 'link' => '/users'],
                    ['title' => 'Total Students', 'value' => User::where('classification', 'Student')->count(), 'icon' => 'fa-user-graduate', 'color' => 'warning', 'link' => '/students'],
                ];
                break;
                
            case 'Division Administrator':
            case 'Division Supervisor':
            case 'Division Superintendent':
            case 'Assistant Division Superintendent':
            case 'Chief of CID':
            case 'Chief of SGOD':
                $division_id = $this->getUserDivisionId($classification);
                $stats = [
                    ['title' => 'Districts', 'value' => DB::table('tbl_districts')->where('division_id', $division_id)->count(), 'icon' => 'fa-map-marker-alt', 'color' => 'primary', 'link' => '/districts'],
                    ['title' => 'Schools', 'value' => DB::table('tbl_schools')->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')->where('division_id', $division_id)->count(), 'icon' => 'fa-school', 'color' => 'success', 'link' => '/schools'],
                    ['title' => 'Teachers', 'value' => User::where('classification', 'Teacher')->count(), 'icon' => 'fa-chalkboard-teacher', 'color' => 'info', 'link' => '/teachers'],
                    ['title' => 'Students', 'value' => User::where('classification', 'Student')->count(), 'icon' => 'fa-user-graduate', 'color' => 'warning', 'link' => '/students'],
                ];
                break;
                
            case 'School Head':
                $school_id = DB::table('tbl_school_supervisors')->where('user_id', Auth::user()->id)->value('school_id');
                $stats = [
                    ['title' => 'Classrooms', 'value' => DB::table('tbl_classrooms')->where('school_id', $school_id)->count(), 'icon' => 'fa-door-open', 'color' => 'primary', 'link' => '/classrooms'],
                    ['title' => 'Teachers', 'value' => DB::table('tbl_teachers')->where('school_id', $school_id)->count(), 'icon' => 'fa-chalkboard-teacher', 'color' => 'success', 'link' => '/teachers'],
                    ['title' => 'Students', 'value' => DB::table('tbl_students')->where('school_id', $school_id)->count(), 'icon' => 'fa-user-graduate', 'color' => 'info', 'link' => '/students'],
                    ['title' => 'Assessments', 'value' => DB::table('tbl_assessments')->count(), 'icon' => 'fa-clipboard-list', 'color' => 'warning', 'link' => '/assessments'],
                ];
                break;
                
            case 'Department Head':
                $head = DB::table('tbl_department_heads')->where('user_id', Auth::user()->id)->first();
                $stats = [
                    ['title' => 'My Classes', 'value' => DB::table('tbl_teacher_classes')->where('school_id', $head->school_id)->count(), 'icon' => 'fa-book', 'color' => 'primary', 'link' => '/classrooms'],
                    ['title' => 'Teachers', 'value' => DB::table('tbl_teachers')->where('school_id', $head->school_id)->count(), 'icon' => 'fa-chalkboard-teacher', 'color' => 'success', 'link' => '/teachers'],
                    ['title' => 'Assessments', 'value' => DB::table('tbl_assessments')->count(), 'icon' => 'fa-clipboard-list', 'color' => 'info', 'link' => '/assessments'],
                    ['title' => 'Reports', 'value' => DB::table('tbl_reports')->count(), 'icon' => 'fa-chart-bar', 'color' => 'warning', 'link' => '/reports'],
                ];
                break;
                
            case 'Teacher':
                $teacher = DB::table('tbl_teachers')->where('user_id', Auth::user()->id)->first();
                $activeAY = DB::table('tbl_academic_years')->where('is_active', 1)->value('id');
                $advisoryCount = DB::table('tbl_teacher_classes')
                    ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
                    ->where('teacher_id', $teacher->id)
                    ->where('advisory', 1)
                    ->where('academic_year_id', $activeAY)
                    ->distinct('tbl_teacher_classes.classroom_id')
                    ->count('tbl_teacher_classes.classroom_id');
                $subjectCount = DB::table('tbl_teacher_classes')
                    ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
                    ->where('teacher_id', $teacher->id)
                    ->where('advisory', 0)
                    ->where('academic_year_id', $activeAY)
                    ->count();
                $studentCount = DB::table('tbl_student_classes')
                    ->join('tbl_teacher_classes', 'tbl_student_classes.class_id', 'tbl_teacher_classes.id')
                    ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
                    ->where('tbl_teacher_classes.teacher_id', $teacher->id)
                    ->where('tbl_classrooms.academic_year_id', $activeAY)
                    ->where('tbl_student_classes.status', 1)
                    ->distinct('tbl_student_classes.student_id')
                    ->count('tbl_student_classes.student_id');
                $stats = [
                    ['title' => 'Advisory Classes', 'value' => $advisoryCount, 'icon' => 'fa-chalkboard-teacher', 'color' => 'primary', 'link' => '/classrooms'],
                    ['title' => 'Subject Classes', 'value' => $subjectCount, 'icon' => 'fa-book', 'color' => 'info', 'link' => '/classrooms'],
                    ['title' => 'Students', 'value' => $studentCount, 'icon' => 'fa-user-graduate', 'color' => 'success', 'link' => '/classrooms'],
                    ['title' => 'Periodicals', 'value' => DB::table('tbl_assessments')->where('teacher_id', $teacher->id)->where('assessment_type_id', 1)->count(), 'icon' => 'fa-clipboard-list', 'color' => 'secondary', 'link' => '/periodicals'],
                    ['title' => 'Summatives', 'value' => DB::table('tbl_assessments')->where('teacher_id', $teacher->id)->where('assessment_type_id', 2)->count(), 'icon' => 'fa-file-alt', 'color' => 'warning', 'link' => '/summatives'],
                ];
                break;
                
            case 'Student':
                $student = DB::table('tbl_students')->where('user_id', Auth::user()->id)->first();
                $stats = [
                    ['title' => 'My Classes', 'value' => DB::table('tbl_student_classes')->where('student_id', $student->id)->count(), 'icon' => 'fa-book', 'color' => 'primary', 'link' => '/students/class_assessments'],
                    ['title' => 'Completed Assessments', 'value' => DB::table('tbl_student_answers')->where('student_id', $student->id)->distinct('assessment_id')->count(), 'icon' => 'fa-check-circle', 'color' => 'success', 'link' => '/students/class_assessments'],
                    ['title' => 'Pending Assessments', 'value' => 0, 'icon' => 'fa-clock', 'color' => 'warning', 'link' => '/students/class_assessments'],
                    ['title' => 'My Scores', 'value' => '--', 'icon' => 'fa-chart-line', 'color' => 'info', 'link' => '/students/class_assessments'],
                ];
                break;
                
            default:
                $stats = [];
        }
        
        return $stats;
    }

    private function getQuickLinks($classification)
    {
        $links = [];
        
        switch ($classification) {
            case 'System Administrator':
                $links = [
                    ['title' => 'Manage Divisions', 'icon' => 'fa-map', 'url' => '/divisions', 'color' => 'primary'],
                    ['title' => 'Manage Users', 'icon' => 'fa-users', 'url' => '/users', 'color' => 'success'],
                    ['title' => 'View Reports', 'icon' => 'fa-chart-bar', 'url' => '/reports', 'color' => 'info'],
                    ['title' => 'System Trails', 'icon' => 'fa-history', 'url' => '/trails', 'color' => 'warning'],
                ];
                break;
                
            case 'Division Administrator':
            case 'Division Supervisor':
            case 'Division Superintendent':
            case 'Assistant Division Superintendent':
            case 'Chief of CID':
            case 'Chief of SGOD':
                $links = [
                    ['title' => 'Manage Schools', 'icon' => 'fa-school', 'url' => '/schools', 'color' => 'primary'],
                    ['title' => 'Manage Users', 'icon' => 'fa-users', 'url' => '/users', 'color' => 'success'],
                    ['title' => 'View Reports', 'icon' => 'fa-chart-bar', 'url' => '/reports', 'color' => 'info'],
                    ['title' => 'Academic Years', 'icon' => 'fa-calendar', 'url' => '/academic_years', 'color' => 'warning'],
                ];
                break;
                
            case 'School Head':
                $links = [
                    ['title' => 'Manage Classrooms', 'icon' => 'fa-door-open', 'url' => '/classrooms', 'color' => 'primary'],
                    ['title' => 'Manage Teachers', 'icon' => 'fa-chalkboard-teacher', 'url' => '/teachers', 'color' => 'success'],
                    ['title' => 'Manage Students', 'icon' => 'fa-user-graduate', 'url' => '/students', 'color' => 'info'],
                    ['title' => 'View Reports', 'icon' => 'fa-chart-bar', 'url' => '/reports', 'color' => 'warning'],
                ];
                break;
                
            case 'Department Head':
                $links = [
                    ['title' => 'My Classrooms', 'icon' => 'fa-door-open', 'url' => '/classrooms', 'color' => 'primary'],
                    ['title' => 'Manage Teachers', 'icon' => 'fa-chalkboard-teacher', 'url' => '/teachers', 'color' => 'success'],
                    ['title' => 'Assessments', 'icon' => 'fa-clipboard-list', 'url' => '/assessments', 'color' => 'info'],
                    ['title' => 'View Reports', 'icon' => 'fa-chart-bar', 'url' => '/reports', 'color' => 'warning'],
                ];
                break;
                
            case 'Teacher':
                $links = [
                    ['title' => 'My Classrooms', 'icon' => 'fa-door-open', 'url' => '/classrooms', 'color' => 'primary'],
                    ['title' => 'Periodical Exams', 'icon' => 'fa-file-alt', 'url' => '/periodicals', 'color' => 'success'],
                    ['title' => 'Summative Tests', 'icon' => 'fa-clipboard-check', 'url' => '/summatives', 'color' => 'info'],
                    ['title' => 'Item Bank', 'icon' => 'fa-database', 'url' => '/item_banks', 'color' => 'warning'],
                ];
                break;
                
            case 'Student':
                $links = [
                    ['title' => 'My Classes', 'icon' => 'fa-book', 'url' => '/students/class_assessments', 'color' => 'primary'],
                    ['title' => 'My Assessments', 'icon' => 'fa-clipboard-list', 'url' => '/students/class_assessments', 'color' => 'success'],
                    ['title' => 'My Scores', 'icon' => 'fa-chart-line', 'url' => '/students/class_assessments', 'color' => 'info'],
                    ['title' => 'My Profile', 'icon' => 'fa-user', 'url' => '/account', 'color' => 'warning'],
                ];
                break;
                
            default:
                $links = [];
        }
        
        return $links;
    }

    private function getRecentActivities($classification)
    {
        // For now, return empty array. Can be expanded later with audit trail data
        return [];
    }

    private function getUserDivisionId($classification)
    {
        switch ($classification) {
            case 'Division Administrator':
                return DB::table('tbl_division_administrators')->where('user_id', Auth::user()->id)->value('division_id');
            case 'Division Supervisor':
                return DB::table('tbl_division_supervisors')->where('user_id', Auth::user()->id)->value('division_id');
            case 'Division Superintendent':
                return DB::table('tbl_division_superintendents')->where('user_id', Auth::user()->id)->value('division_id');
            case 'Assistant Division Superintendent':
                return DB::table('tbl_asst_division_superintendents')->where('user_id', Auth::user()->id)->value('division_id');
            case 'Chief of CID':
                return DB::table('tbl_chief_cids')->where('user_id', Auth::user()->id)->value('division_id');
            case 'Chief of SGOD':
                return DB::table('tbl_chief_sgods')->where('user_id', Auth::user()->id)->value('division_id');
            default:
                return null;
        }
    }

    private function getReports($classification)
    {
        $reports = [];
        
        switch ($classification) {
            case 'System Administrator':
                $reports = [
                    ['title' => 'All Schools Report', 'icon' => 'fa-school', 'url' => '/reports/schools', 'description' => 'Complete list of all schools'],
                    ['title' => 'All Teachers Report', 'icon' => 'fa-chalkboard-teacher', 'url' => '/reports/teachers', 'description' => 'Teacher masterlist'],
                    ['title' => 'All Students Report', 'icon' => 'fa-user-graduate', 'url' => '/reports/students', 'description' => 'Student masterlist'],
                    ['title' => 'System Activity Log', 'icon' => 'fa-history', 'url' => '/reports/activity-log', 'description' => 'System-wide activity logs'],
                ];
                break;
                
            case 'Division Administrator':
            case 'Division Superintendent':
            case 'Assistant Division Superintendent':
            case 'Chief of CID':
            case 'Chief of SGOD':
                $reports = [
                    ['title' => 'Schools Report', 'icon' => 'fa-school', 'url' => '/reports/schools', 'description' => 'Division schools report'],
                    ['title' => 'Teachers Report', 'icon' => 'fa-chalkboard-teacher', 'url' => '/reports/teachers', 'description' => 'Division teachers report'],
                    ['title' => 'Students Report', 'icon' => 'fa-user-graduate', 'url' => '/reports/students', 'description' => 'Division students report'],
                    ['title' => 'Assessment Summary', 'icon' => 'fa-chart-bar', 'url' => '/reports/assessments', 'description' => 'Division-wide assessment data'],
                ];
                break;
                
            case 'School Head':
                $reports = [
                    ['title' => 'School Teachers Report', 'icon' => 'fa-chalkboard-teacher', 'url' => '/reports/school-teachers', 'description' => 'Teachers in my school'],
                    ['title' => 'School Students Report', 'icon' => 'fa-user-graduate', 'url' => '/reports/school-students', 'description' => 'Students in my school'],
                    ['title' => 'Classroom Report', 'icon' => 'fa-door-open', 'url' => '/reports/classrooms', 'description' => 'All classrooms and sections'],
                    ['title' => 'Assessment Summary', 'icon' => 'fa-clipboard-check', 'url' => '/reports/school-assessments', 'description' => 'School assessment overview'],
                ];
                break;
                
            case 'Department Head':
                $reports = [
                    ['title' => 'Department Teachers', 'icon' => 'fa-users', 'url' => '/reports/dept-teachers', 'description' => 'Teachers in my department'],
                    ['title' => 'Department Classes', 'icon' => 'fa-book', 'url' => '/reports/dept-classes', 'description' => 'Classes under my department'],
                    ['title' => 'Assessment Report', 'icon' => 'fa-chart-line', 'url' => '/reports/dept-assessments', 'description' => 'Department assessment data'],
                ];
                break;
                
            case 'Teacher':
                $reports = [
                    ['title' => 'My Classes Report', 'icon' => 'fa-book', 'url' => '/reports/my-classes', 'description' => 'All my classes and students'],
                    ['title' => 'Student List', 'icon' => 'fa-users', 'url' => '/reports/my-students', 'description' => 'Complete student roster'],
                    ['title' => 'Periodicals Report', 'icon' => 'fa-clipboard-list', 'url' => '/reports/my-periodicals', 'description' => 'My periodical exam records'],
                    ['title' => 'Summatives Report', 'icon' => 'fa-file-alt', 'url' => '/reports/my-summatives', 'description' => 'My summative assessment records'],
                    ['title' => 'Class Performance', 'icon' => 'fa-chart-bar', 'url' => '/reports/class-performance', 'description' => 'Student performance analysis'],
                ];
                break;
                
            case 'Student':
                $reports = [
                    ['title' => 'My Grades', 'icon' => 'fa-star', 'url' => '/reports/my-grades', 'description' => 'View my grades and assessments'],
                    ['title' => 'Assessment History', 'icon' => 'fa-history', 'url' => '/reports/my-assessments', 'description' => 'My assessment records'],
                ];
                break;
        }
        
        return $reports;
    }

    public function database(){

        $lists = DB::select('SHOW TABLES');
        foreach ($lists as $key => $list) {
            $tables[$list->Tables_in_db_stars_2022] = Schema::getColumnListing($list->Tables_in_db_stars_2022);
        }
        
        return view('db', compact('tables'));
    }

    public function truncate($key){

        if($key == "parasabayan2022"){
            CustomFunction::truncate();
            return redirect('/dashboard')->with('success', 'Table Successfully truncated!');
        }else{

            return redirect('/forbidden');
        }
        
    }

    public function download_template($code){
        
        $templates = $this->templates();
        $file = public_path('uploaders/'.$templates[$code].'.xlsx');
        return Response::download($file); 
    }

    
    public function templates(){
        $templates = array(
            'ACU' => "ADVISORY-CLASS-UPLOADER",
            'ACU-SHS' => "ADVISORY-CLASS-UPLOADER-SHS-NEW",
            'CU' => "COMPETENCY-UPLOADER",
            'SHU' => "SCHOOL-HEAD-UPLOADER",
            'SCU' => "SUBJECT-CLASS-UPLOADER-2",
            'SU' => "SECTION-UPLOADER",
            'TAU' => "TEACHER-ACCOUNT-UPLOADER",
            'ANS-KEY' => "ANSWER-KEY-UPLOADER",
        );

        return $templates;
    }
    
}
