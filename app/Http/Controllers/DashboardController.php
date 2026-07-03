<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CustomFunction;
use App\Models\School;
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
        $students = User::where('classification', 'Student')->count();
        $templates = $this->templates();
        $classification = Auth::user()->classification;

        return view('layouts.dashboard', compact('page', 'academic_year', 'details',
            'users', 'schools', 'students', 'templates', 'classification'

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
