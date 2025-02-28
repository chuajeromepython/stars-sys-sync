<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\AssistantDivisionSuperIntendent;
use App\Models\Person;
use App\Models\Subject;
use App\Models\Division;
use App\Models\User;
use Auth;

class AssistantDivisionSuperIntendentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AssistantDivisionSuperIntendent  $assistantDivisionSuperIntendent
     * @return \Illuminate\Http\Response
     */
    public function show(AssistantDivisionSuperIntendent $assistantDivisionSuperIntendent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AssistantDivisionSuperIntendent  $assistantDivisionSuperIntendent
     * @return \Illuminate\Http\Response
     */
    public function edit(AssistantDivisionSuperIntendent $asst_division_superintendent)
    {
        $page = [
            'name'      =>  'User',
            'title'     =>  'Edit Division Superintendent',
            'crumb'     =>  array('Users' => '/users', "Edit Division Superintendent" => "")
        ];

        $user = User::find($asst_division_superintendent->user_id);
        $person = Person::find($user->person_id);
        switch (Auth::user()->classification) {
            case 'Division Administrator':
                $divisions = Division::where('id', $divisionSupervisor->division_id)->get();
                $attribute = "disabled";
                break;
            case 'System Administrator':
                $divisions = Division::all();
                $attribute = "";
                break;
            default:
                break;
        }

        return view('asst_division_superintendents.edit', compact(
            'page', 'user', 'person', 
            'divisions', 'asst_division_superintendent', 'attribute'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AssistantDivisionSuperIntendent  $assistantDivisionSuperIntendent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();
        
        try {
            
            $asst_division_superintendent = AssistantDivisionSuperIntendent::find($request->id);
            $user = User::find($asst_division_superintendent->user_id);
            $person = Person::find($user->person_id);
            
            if (Auth::user()->classification == "System Administrator") {
                $asst_division_superintendent->division_id = $request->division;
                $asst_division_superintendent->save();
            }
            
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

         if($result === true) {
            return back()->with('success', 'Assistant Division Superintendent has been updated successfully.');
        } else {
            return back()->withErrors($result);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AssistantDivisionSuperIntendent  $assistantDivisionSuperIntendent
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssistantDivisionSuperIntendent $assistantDivisionSuperIntendent)
    {
        //
    }
}
