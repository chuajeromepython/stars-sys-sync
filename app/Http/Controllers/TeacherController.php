<?php

namespace App\Http\Controllers;

use App\Models\ClassificationHistory;
use App\Models\CustomFunction;
use App\Models\DivisionAdministrator;
use App\Models\Person;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\Teacher;
use App\Models\TeacherClass;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Teacher',
            'title' => 'Teacher Management',
            'crumb' => ['Teacher' => '/teachers'],
        ];

        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $teachers = User::select(
            'tbl_teachers.id', 'username', 'user_id',
            'first_name', 'middle_name', 'last_name', 'suffix'
        )->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('classification', 'Teacher')
            ->where('school_id', $school_id)
            ->get();

        return view('teachers.index', compact(
            'page',
            'teachers',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $page = [
            'name' => 'Teacher',
            'title' => 'Teacher Management',
            'crumb' => ['Teacher' => '/teachers', 'Add Teacher' => '/teachers/create'],
        ];

        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');

        $school = School::find($school_id);

        // dd($school->name);
        return view('teachers.create', compact(
            'page',
            'school',
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $check_username = User::where('username', '=', $request->username)->get();

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'birth_date' => 'required',
            'gender' => 'required',
        ]);

        if ($check_username->count() == 0) {

            DB::beginTransaction();
            try {

                $person = new Person;
                $person->first_name = $request->first_name;
                $person->middle_name = $request->middle_name;
                $person->last_name = $request->last_name;
                $person->suffix = $request->suffix;
                $person->gender = $request->gender;
                $person->birth_date = date('Y-m-d', strtotime($request->birth_date));
                $person->save(); // insert

                $user = new User;
                $user->username = $request->username;
                $user->password = bcrypt('12345');
                $user->classification = 'Teacher';
                $user->status = true;
                $user->person_id = $person->id;
                $user->save();

                $teacher = new Teacher;
                $teacher->email = $request->username;
                $teacher->user_id = $user->id;
                $teacher->school_id = $request->school_id;
                $teacher->save();

                DB::commit();
                $result = true;

            } catch (Exception $e) {
                DB::rollBack();
                $result = $e->getMessage();
            }

            if ($result === true) {
                return back()->with('success', 'New teacher has been added successfully.');
            } else {
                return back()->withErrors($result);
            }

        } else {
            return back()->withErrors('Username is already taken.')->withInput($request->all);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Teacher $teacher)
    {
        $page = [
            'name' => (Auth::user()->classification == 'School Head') ? 'Teacher' : 'User',
            'title' => 'Edit Teacher',
            'crumb' => ['Users' => '/users', 'Edit Teacher' => ''],
        ];

        if (Auth::user()->classification == 'School Head') {
            $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
            $schools = School::where('id', $school_id)->get();
        } elseif (Auth::user()->classification == 'Division Administrator') {
            $division_id = DivisionAdministrator::find(Auth::user()->id)->value('division_id');
            $schools = School::select('tbl_schools.name', 'tbl_schools.id')
                ->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
                ->where('division_id', $division_id)
                ->get();
        } elseif (Auth::user()->classification == 'System Administrator') {
            $schools = School::all();
        } else {
            return redirect('/forbidden');
        }

        $user = User::find($teacher->user_id);
        $person = Person::find($user->person_id);

        return view('teachers.edit', compact(
            'page', 'user', 'person',
            'schools', 'teacher'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Teacher  $teacher
     * @return Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();

        try {

            $teacher = Teacher::find($request->id);
            $user = User::find($teacher->user_id);
            $person = Person::find($user->person_id);

            if (Auth::user()->classification != 'School Head') {
                $teacher->school_id = $request->school_id;
            }
            $teacher->email = $request->email;
            $teacher->save();

            $person->first_name = $request->first_name;
            $person->middle_name = $request->middle_name;
            $person->last_name = $request->last_name;
            $person->suffix = $request->suffix;
            $person->birth_date = $request->birth_date;
            $person->gender = $request->gender;
            $person->save();

            DB::commit();
            $result = true;

        } catch (Exception  $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if ($result === true) {
            return back()->with('success', 'Teacher has been updated successfully.');
        } else {
            return back()->withErrors($result);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Teacher  $teacher
     * @return Response
     */
    public function destroy(Request $request)
    {
        $user = User::find($request->id);
        $teacher = Teacher::where('user_id', $user->id)->first();
        $has_record = TeacherClass::where('teacher_id', $teacher->id)->get();

        if ($has_record->count() > 0) {
            return back()->withErrors('Teacher "'.$user->username.'" has Class Record.');
        } else {
            $user->delete();
        }

        return back()->with('success', 'User account has been deleted successfully.');
    }

    public function upload(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'Please upload a file.',
            'file.mimes' => 'The file must be an Excel file (xlsx or xls).',
            'file.max' => 'The file size must not exceed 10MB.',
        ]);

        $spreadsheet = IOFactory::load($request->file('file'));
        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $sheet = $spreadsheet->getActiveSheet()->toArray();
        $data = [];
        $errors = [];
        $error_messages = [];

        DB::beginTransaction();
        try {

            foreach ($sheet as $key => $row) {
                if ($key > 1) {

                    if ($row[0] == null) {
                        break;
                    }
                    $data = [
                        'last_name' => $row[0],
                        'first_name' => $row[1],
                        'middle_name' => $row[2],
                        'suffix' => $row[3],
                        'email' => $row[4],
                        'gender' => $row[5],
                        'birth_date' => $row[6],
                    ];

                    $get_errors = CustomFunction::getTeacherUploaderError($data, $key);

                    if (count($get_errors) > 0) {
                        $errors[] = $get_errors;
                    } else {

                        $person = new Person;
                        $person->first_name = $data['first_name'];
                        $person->middle_name = $data['middle_name'];
                        $person->last_name = $data['last_name'];
                        $person->suffix = $data['suffix'];
                        $person->gender = ($data['gender'] == 'Female') ? 'F' : 'M';
                        $person->birth_date = ($data['birth_date'] == null) ? null : date('Y-m-d', strtotime($data['birth_date']));
                        $person->save(); // insert

                        $user = new User;
                        $user->username = $data['email'];
                        $user->password = bcrypt('12345');
                        $user->classification = 'Teacher';
                        $user->status = true;
                        $user->person_id = $person->id;
                        $user->save();

                        $history = new ClassificationHistory;
                        $history->user_id = $user->id;
                        $history->classification = 'Teacher';
                        $history->encoder_user_id = Auth::user()->id;
                        $history->save();

                        $teacher = new Teacher;
                        $teacher->email = $data['email'];
                        $teacher->user_id = $user->id;
                        $teacher->school_id = $school_id;
                        $teacher->save();
                    }
                }
            }

            if (count($errors) > 0) {
                foreach ($errors as $error_msgs) {
                    foreach ($error_msgs as $key => $message) {
                        $error_messages[] = $message;
                    }
                }

                return back()->withErrors($error_messages);
            }

            DB::commit();
            $result = true;

        } catch (Exception $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if ($result === true) {
            return redirect('/teachers')->with('success', 'Teacher Uploader has been uploaded successfully.');
        } else {
            return back()->withErrors($result);
        }

    }
}
