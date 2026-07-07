<?php

namespace App\Http\Controllers;

use App\Models\ClassificationHistory;
use App\Models\CustomFunction;
use App\Models\DivisionAdministrator;
use App\Models\Person;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SchoolSupervisorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    public function edit(SchoolSupervisor $school_supervisor)
    {
        $page = [
            'name' => 'User',
            'title' => 'Edit School Head',
            'crumb' => ['Users' => '/users', 'Edit School Head' => ''],
        ];

        $user = User::find($school_supervisor->user_id);
        $person = Person::find($user->person_id);

        switch (Auth::user()->classification) {
            case 'Division Administrator':
                $division_id = DivisionAdministrator::find(Auth::user()->id)->value('division_id');
                $schools = School::select('tbl_schools.name', 'tbl_schools.id')
                    ->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
                    ->where('division_id', $division_id)
                    ->get();
                break;
            case 'System Administrator':
                $schools = School::all();
                break;
            default:
                break;
        }

        return view('school_supervisors.edit', compact(
            'page', 'user', 'person',
            'schools', 'school_supervisor'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  SchoolSupervisor  $schoolSupervisor
     * @return Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();

        try {

            $school_supervisor = SchoolSupervisor::find($request->id);
            $user = User::find($school_supervisor->user_id);
            $person = Person::find($user->person_id);

            $school_supervisor->school_id = $request->school_id;
            $school_supervisor->save();

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
            return back()->with('success', 'School Supervisor has been updated successfully.');
        } else {
            return back()->withErrors($result);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(SchoolSupervisor $schoolSupervisor)
    {
        //
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
                        'school_id' => $row[7],
                    ];

                    $get_errors = CustomFunction::getSchoolHeadUploaderError($data, $key);

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
                        $user->classification = 'School Head';
                        $user->status = true;
                        $user->person_id = $person->id;
                        $user->save();

                        $history = new ClassificationHistory;
                        $history->user_id = $user->id;
                        $history->classification = 'School Head';
                        $history->encoder_user_id = Auth::user()->id;
                        $history->save();

                        $school = School::where('code', $data['school_id'])->first();

                        $supervisor = new SchoolSupervisor;
                        $supervisor->email = $data['email'];
                        $supervisor->user_id = $user->id;
                        $supervisor->school_id = $school->id;
                        $supervisor->status = 1;
                        $supervisor->save();
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
            return redirect('/users')->with('success', 'School Head Uploader has been uploaded successfully.');
        } else {
            return back()->withErrors($result);
        }
    }
}
