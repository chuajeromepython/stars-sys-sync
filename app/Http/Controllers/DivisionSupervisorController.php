<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\DivisionSupervisor;
use App\Models\Person;
use App\Models\Subject;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DivisionSupervisorController extends Controller
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

    public function edit(DivisionSupervisor $divisionSupervisor)
    {
        $page = [
            'name' => 'User',
            'title' => 'Edit Division Supervisor',
            'crumb' => ['Users' => '/users', 'Edit Division Supervisor' => ''],
        ];

        $user = User::find($divisionSupervisor->user_id);
        $person = Person::find($user->person_id);

        $current_subjects = array_filter(json_decode($divisionSupervisor->subject_id, true));
        $subjects = Subject::all();

        switch (Auth::user()->classification) {
            case 'Division Administrator':
                $divisions = Division::where('id', $divisionSupervisor->division_id)->get();
                $attribute = 'disabled';
                break;
            case 'System Administrator':
                $divisions = Division::all();
                $attribute = '';
                break;
            default:
                break;
        }

        return view('division_supervisors.edit', compact(
            'page',
            'user',
            'person',
            'divisions',
            'current_subjects',
            'subjects',
            'divisionSupervisor',
            'attribute'
        ));
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {

            $division_supervisor = DivisionSupervisor::find($request->id);
            $user = User::find($division_supervisor->user_id);
            $person = Person::find($user->person_id);

            if (Auth::user()->classification == 'System Administrator') {
                $division_supervisor->division_id = $request->division;
            }

            $division_supervisor->subject_id = json_encode($request->subjects);
            $division_supervisor->save();

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
            return back()->with('success', 'Division Supervisor has been updated successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => $result]);
        }

    }
}
