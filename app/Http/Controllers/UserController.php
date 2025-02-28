<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use Auth;
use App\Models\AssistantDivisionSuperIntendent;
use App\Models\ChiefCID;
use App\Models\ChiefSGOD;
use App\Models\Classification;
use App\Models\ClassificationHistory;
use App\Models\CustomFunction;
use App\Models\DepartmentHead;
use App\Models\District;
use App\Models\DistrictSupervisor;
use App\Models\Division;
use App\Models\DivisionSupervisor;
use App\Models\DivisionAdministrator;
use App\Models\DivisionSuperIntendent;
use App\Models\Person;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $page = [
            'name'      =>  'User',
            'title'     =>  'User Management',
            'crumb'     =>  array('Users' => '/users')
        ];

        $filter = CustomFunction::filterViewClassification();
        $classifications = Classification::whereIn('classification', $filter)->get();

        $users = $this->datatable($request);
        return view('users.index', compact(
            'page', 
            'users',
            'classifications','request'
        ));
    }

        
    public function create()
    {
        
        $page = [
            'name'      =>  'User',
            'title'     =>  'Add User',
            'crumb'     =>  array('Users' => '/users', "Add User" => "")
        ];

        
        $options = CustomFunction::dynamicAreaOption();
        $filter = CustomFunction::filterCreateClassification();
        $clasf = Classification::whereIn('classification', $filter)->get();
        $classification = Auth::user()->classification;

        return view('users.create', compact(
            'page', 'clasf', 'options'
        ));
    }

    public function edit(User $user){

        switch ($user->classification) {

            case 'Division Supervisor':
                $id = DivisionSupervisor::where('user_id', $user->id)->value('id');
                return redirect('/division_supervisors/'.$id.'/edit');
                break;
            case 'Division Administrator':
                $id = DivisionAdministrator::where('user_id', $user->id)->value('id');
                return redirect('/division_administrators/'.$id.'/edit');
                break;
            case 'District Supervisor':
                $id = DistrictSupervisor::where('user_id', $user->id)->value('id');
                return redirect('/district_supervisors/'.$id.'/edit');
                break;
            case 'School Head':
                $id = SchoolSupervisor::where('user_id', $user->id)->value('id');
                return redirect('/school_supervisors/'.$id.'/edit');
                break;
            case 'Division Superintendent':
                $id = DivisionSuperIntendent::where('user_id', $user->id)->value('id');
                return redirect('/division_superintendents/'.$id.'/edit');
                break;
            case 'Assistant Division Superintendent':
                $id = AssistantDivisionSuperIntendent::where('user_id', $user->id)->value('id');
                return redirect('/asst_division_superintendents/'.$id.'/edit');
                break;
            case 'Chief of CID':
                $id = ChiefCID::where('user_id', $user->id)->value('id');
                return redirect('/chief_cids/'.$id.'/edit');
                break;
            case 'Chief of SGOD':
                $id = ChiefSGOD::where('user_id', $user->id)->value('id');
                return redirect('/chief_sgods/'.$id.'/edit');
                break;
            case 'Department Head':
                $id = DepartmentHead::where('user_id', $user->id)->value('id');
                return redirect('/department_heads/'.$id.'/edit');
                break;
            case 'Teacher':
                $id = Teacher::where('user_id', $user->id)->value('id');
                return redirect('/teachers/'.$id.'/edit');
                break;
            default:
                return redirect('/forbidden');
                break;
        }

    }


    public function store(Request $request)
    {   
        $check_username = User::where('username', '=', $request->username)->get();
        
        if($check_username->count() == 0){

            DB::beginTransaction();
            try {
                
                $person = new Person;
                $person->first_name = $request->first_name;
                $person->middle_name = $request->middle_name;
                $person->last_name = $request->last_name;
                $person->suffix = $request->suffix;
                $person->gender = $request->gender;
                $person->birth_date = date("Y-m-d", strtotime($request->birth_date));
                $person->save(); // insert 

                $user = new User; 
                $user->username = $request->username;
                $user->password = bcrypt('12345');
                $user->classification = $request->classification;
                $user->status = true;
                $user->person_id = $person->id;
                $user->save();

                $history = new ClassificationHistory; 
                $history->user_id = $user->id;
                $history->classification = $request->classification;
                $history->encoder_user_id = Auth::user()->id;
                $history->save();

                $encoder_classification = Auth::user()->classification;

                switch ($encoder_classification) {

                    case 'System Administrator':
                        $supervisor = new DivisionAdministrator;
                        $supervisor->status = true;
                        $supervisor->user_id = $user->id;
                        $supervisor->division_id = $request->division;
                        $supervisor->email =  $request->username;
                        $supervisor->save();
                        break;

                    case 'Division Administrator':
                        if ($request->district){
                            $supervisor = new DistrictSupervisor;
                            $supervisor->status = true;
                            $supervisor->user_id = $user->id;
                            $supervisor->district_id = $request->district;
                            $supervisor->email =  $request->username;
                            $supervisor->save();
                        } 
                        if ($request->school_id) {
                            $supervisor = new SchoolSupervisor;
                            $supervisor->status = true;
                            $supervisor->user_id = $user->id;
                            $supervisor->school_id = $request->school_id;
                            $supervisor->email =  $request->username;
                            $supervisor->save();
                        }
                        if ($request->division) {
                            if ($request->classification == "Division Supervisor") {
                                $supervisor = new DivisionSupervisor;
                                $supervisor->subject_id = json_encode($request->subjects);
                            } elseif ($request->classification == "Division Superintendent") {
                                $supervisor = new DivisionSuperIntendent;
                            } elseif ($request->classification == "Assistant Division Superintendent") {
                                $supervisor = new AssistantDivisionSuperIntendent;
                            } elseif ($request->classification == "Chief of CID") {
                                $supervisor = new ChiefCID;
                            } elseif ($request->classification == "Chief of SGOD") {
                                $supervisor = new ChiefSGOD;
                            }

                            $supervisor->status = true;
                            $supervisor->user_id = $user->id;
                            $supervisor->division_id = $request->division;
                            $supervisor->email =  $request->username;
                            $supervisor->save();
                        }
                        break;
                    case 'School Head':
                        break;
                    default:
                        break;
                }
                
                DB::commit();
                $result = true;

            } catch (Exception $e) {
                DB::rollBack();
                $result = $e->getMessage();
            }

            if($result === true) {
                return redirect('/users')->with('success', 'New user has been added successfully.');
            } else {
                return back()->withErrors($result);
            }

        }else{
            return back()->withErrors('Username is already taken.')->withInput($request->all);
        }

    }

    public function datatable($request)
    {


        $users = User::join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id');
        $encoder  = Auth::user()->classification;

        $select_array = [
            "tbl_users.id as id", 
            'classification', 'username', 
            'first_name', 
            'middle_name',
            'last_name', 
            'username'
        ];

        if($request->classification != null){
            if($request->keyword != null){
                $users = $users->where(
                    DB::raw(
                        "CONCAT(
                            `tbl_persons`.`first_name`,
                            `tbl_persons`.`middle_name`,
                            `tbl_persons`.`last_name`
                        )"
                    ),  'LIKE', "%". $request->keyword."%"
                );
            }

            switch ($request->classification) {
                case 'Division Supervisor':
                    $users = $users->join('tbl_division_supervisors', 'tbl_division_supervisors.user_id', 'tbl_users.id')
                        ->join('tbl_divisions', 'tbl_division_supervisors.division_id', 'tbl_divisions.id');
                        array_push($select_array, "tbl_divisions.name as area");
                        $area = "tbl_divisions.name";
                    break;
                case 'Division Administrator':
                    $users = $users->join('tbl_division_administrators', 'tbl_division_administrators.user_id', 'tbl_users.id')
                        ->join('tbl_divisions', 'tbl_division_administrators.division_id', 'tbl_divisions.id');
                        array_push($select_array, "tbl_divisions.name as area");
                        $area = "tbl_divisions.name";
                    break;
                case 'Division Superintendent':
                    $users = $users->join('tbl_division_superintendents', 'tbl_division_superintendents.user_id', 'tbl_users.id')
                        ->join('tbl_divisions', 'tbl_division_superintendents.division_id', 'tbl_divisions.id');
                        array_push($select_array, "tbl_divisions.name as area");
                        $area = "tbl_divisions.name";
                    break;
                case 'Assistant Division Superintendent':
                    $users = $users->join('tbl_asst_division_superintendents', 'tbl_asst_division_superintendents.user_id', 'tbl_users.id')
                        ->join('tbl_divisions', 'tbl_asst_division_superintendents.division_id', 'tbl_divisions.id');
                        array_push($select_array, "tbl_divisions.name as area");
                        $area = "tbl_divisions.name";
                    break;
                case 'Chief of CID':
                    $users = $users->join('tbl_chief_cids', 'tbl_chief_cids.user_id', 'tbl_users.id')
                        ->join('tbl_divisions', 'tbl_chief_cids.division_id', 'tbl_divisions.id');
                        array_push($select_array, "tbl_divisions.name as area");
                        $area = "tbl_divisions.name";
                    break;
                case 'Chief of SGOD':
                    $users = $users->join('tbl_chief_sgods', 'tbl_chief_sgods.user_id', 'tbl_users.id')
                        ->join('tbl_divisions', 'tbl_chief_sgods.division_id', 'tbl_divisions.id');
                        array_push($select_array, "tbl_divisions.name as area");
                        $area = "tbl_divisions.name";
                    break;
                case 'District Supervisor':
                    $users = $users->join('tbl_district_supervisors', 'tbl_district_supervisors.user_id', 'tbl_users.id')
                        ->join('tbl_districts', 'tbl_district_supervisors.district_id', 'tbl_districts.id');
                        array_push($select_array, "tbl_districts.name as area");
                        $area = "tbl_districts.name";
                    break;
                case 'Teacher':
                    if ($encoder == "School Head") {
                        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
                        $users = $users->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
                        ->join('tbl_schools', 'tbl_teachers.school_id', 'tbl_schools.id')
                        ->where('school_id', $school_id);
                    }else{
                        $users = $users->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
                        ->join('tbl_schools', 'tbl_teachers.school_id', 'tbl_schools.id')
                        ->join('tbl_districts',  'tbl_schools.district_id', 'tbl_districts.id');
                    }

                    array_push($select_array, "tbl_schools.name as area");
                    $area = "tbl_schools.name";

                    break;
                case 'School Head':
                    $users = $users->join('tbl_school_supervisors', 'tbl_school_supervisors.user_id', 'tbl_users.id')
                        ->join('tbl_schools', 'tbl_school_supervisors.school_id', 'tbl_schools.id')
                        ->join('tbl_districts',  'tbl_schools.district_id', 'tbl_districts.id');
                        array_push($select_array, "tbl_schools.name as area");
                        $area = "tbl_schools.name";

                    break;
                case 'Department Head':
                    $users = $users->join('tbl_department_heads', 'tbl_department_heads.user_id', 'tbl_users.id')
                        ->join('tbl_schools', 'tbl_department_heads.school_id', 'tbl_schools.id')
                        ->join('tbl_districts',  'tbl_schools.district_id', 'tbl_districts.id');
                        array_push($select_array, "tbl_schools.name as area");
                        $area = "tbl_schools.name";

                    break;
                case 'Student':
                    $users = $users->join('tbl_students', 'tbl_students.user_id', 'tbl_users.id')
                        ->join('tbl_schools', 'tbl_students.school_id', 'tbl_schools.id')
                        ->join('tbl_districts',  'tbl_schools.district_id', 'tbl_districts.id');
                        array_push($select_array, "tbl_schools.name as area");
                        $area = "tbl_schools.name";
                    break;
                default:
                    // code...
                    break;
            }
            
            $users = $users->where('classification', $request->classification);
            if($request->area != null){
                $users = $users->where($area, $request->area);
            }
            
        }else{
            if ($encoder == "System Administrator" || $encoder == "Division Administrator") {
                $users = $users = $users->join('tbl_division_supervisors', 'tbl_division_supervisors.user_id', 'tbl_users.id')->join('tbl_divisions', 'tbl_division_supervisors.division_id', 'tbl_divisions.id');
            }
            if ($encoder == "School Head") {
                $school = SchoolSupervisor::where('user_id', Auth::user()->id)->first();
                $users = $users->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
                ->join('tbl_schools', 'tbl_teachers.school_id', 'tbl_schools.id')
                ->where('school_id', $school->school_id);
            }
            

        }
        
        if($encoder == "Division Administrator"){
            $division_id = DivisionAdministrator::where('user_id', Auth::user()->id)->value('division_id');
            $users = $users->where('division_id', $division_id);
        }

        // Discovered on November 11, 2023 - Rein and Glenn
        // if ($encoder == "System Administrator") {
        //     $users = $users->select($select_array)
        //     ->where('tbl_users.status', 1)
        //     ->paginate(10);
        // }else{
                // $users = $users->select($select_array)
                // ->where('tbl_users.status', 1)
                // ->paginate(10);
        // }

        $users = $users->select($select_array)
        ->where('tbl_users.status', 1)
        ->paginate(10);

        $results = array();

        return $users;

    }

    public function reset(Request $request){

        $user = User::find($request->id);
        $user->password = bcrypt('12345');
        $user->save();

        return back()->with('success', $user->username."'s password has been reset successfully." );

    }


    public function destroy(Request $request){

        $user = User::find($request->id);
        $user->delete();

        return back()->with('success', "User account has been deleted successfully." );

    }


    public function show(User $user){

        $page = [
            'name'      =>  'User',
            'title'     =>  'User Details',
            'crumb'     =>  array('Users' => '/users')
        ];


        $histories = CustomFunction::getUserClassificationHistory($user->id);
        $details = CustomFunction::getUserDetails($user->id);
        $options = CustomFunction::dynamicAreaOption();
        $filter = ["System Administrator", $user->classification, "Student"];
        $classifications = Classification::whereNotIn('classification', $filter)->get();
        return view('users.show', compact(
            'page', 
            'histories', 'user', 'details', 'classifications', 'options'
        ));
    }

}
