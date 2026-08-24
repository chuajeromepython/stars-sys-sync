<?php

namespace App\Http\Controllers;

use App\Models\ClassificationHistory;
use App\Models\DepartmentHead;
use App\Models\DivisionAdministrator;
use App\Models\Person;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\Subject;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DepartmentHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Department Head',
            'title' => 'Department Head Management',
            'crumb' => ['Department Head' => '/department_heads'],
        ];

        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $department_heads = User::select(
            'tbl_department_heads.id', 'username', 'tbl_department_heads.subject_id',
            'first_name', 'middle_name', 'last_name', 'suffix', 'user_id'
        )->join('tbl_department_heads', 'tbl_department_heads.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('school_id', $school_id)
            ->where('classification', 'Department Head')
            ->get();

        $subjects = [];

        foreach ($department_heads as $key => $dp) {
            $current_subjects = ($dp->subject_id == 'null') ? [] : array_filter(json_decode($dp->subject_id));
            $get_subject = Subject::whereIn('id', $current_subjects)->get();
            $subjects[$dp->id] = $get_subject;
        }

        return view('department_heads.index', compact(
            'page',
            'department_heads', 'subjects'
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
            'name' => 'Department Head',
            'title' => 'Department Head Management',
            'crumb' => ['Department Head' => '/department_heads', 'Add Department Head' => '/Department Heads/create'],
        ];

        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');

        $school = School::find($school_id);
        $subjects = Subject::all();

        // dd($school->name);
        return view('department_heads.create', compact(
            'page',
            'school', 'subjects'
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
                $user->classification = 'Department Head';
                $user->status = true;
                $user->person_id = $person->id;
                $user->save();

                $history = new ClassificationHistory;
                $history->user_id = $user->id;
                $history->classification = 'Department Head';
                $history->encoder_user_id = Auth::user()->id;
                $history->save();

                $department_head = new DepartmentHead;
                $department_head->email = $request->username;
                $department_head->user_id = $user->id;
                $department_head->school_id = $request->school_id;
                $department_head->subject_id = json_encode($request->subject_id, true);
                $department_head->save();

                DB::commit();
                $result = true;

            } catch (Exception $e) {
                DB::rollBack();
                $result = $e->getMessage();
            }

            if ($result === true) {
                return back()->with('success', 'New Department Head has been added successfully.');
            } else {
                return redirect()->to(url()->previous())->withErrors(['error' => $result]);
            }

        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Username is already taken.'])->withInput($request->all);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(DepartmentHead $departmentHead)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  DepartmentHead  $departmentHead
     * @return Response
     */
    public function edit(DepartmentHead $department_head)
    {
        $page = [
            'name' => (Auth::user()->classification == 'School Head') ? 'Department Head' : 'User',
            'title' => 'Edit Department Head',
            'crumb' => ['Users' => '/users', 'Edit Department Head' => ''],
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

        $subjects = Subject::all();
        $current_subjects = ($department_head->subject_id == 'null') ? [] : array_filter(json_decode($department_head->subject_id));
        $user = User::find($department_head->user_id);
        $person = Person::find($user->person_id);

        return view('department_heads.edit', compact(
            'page', 'user', 'person',
            'schools', 'department_head', 'current_subjects', 'subjects'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  DepartmentHead  $departmentHead
     * @return Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();

        try {

            $department_head = DepartmentHead::find($request->id);
            $user = User::find($department_head->user_id);
            $person = Person::find($user->person_id);

            if (Auth::user()->classification != 'School Head') {
                $department_head->school_id = $request->school_id;
            }

            $department_head->email = $request->email;
            $department_head->subject_id = json_encode($request->subjects, true);
            $department_head->save();

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
            return back()->with('success', 'Department Head has been updated successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => $result]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  DepartmentHead  $departmentHead
     * @return Response
     */
    public function destroy(Request $request)
    {

        $user = User::find($request->id);
        $user->delete();

        return back()->with('success', 'User account has been deleted successfully.');
    }
}
