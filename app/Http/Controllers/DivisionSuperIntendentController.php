<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\DivisionSuperIntendent;
use App\Models\Person;
use App\Models\Division;
use App\Models\User;
use Auth;


class DivisionSuperIntendentController extends Controller
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
     * @param  \App\Models\DivisionSuperIntendent  $divisionSuperIntendent
     * @return \Illuminate\Http\Response
     */
    public function show(DivisionSuperIntendent $divisionSuperIntendent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DivisionSuperIntendent  $divisionSuperIntendent
     * @return \Illuminate\Http\Response
     */
    public function edit(DivisionSuperIntendent $division_superintendent)
    {
        $page = [
            'name'      =>  'User',
            'title'     =>  'Edit Division Superintendent',
            'crumb'     =>  array('Users' => '/users', "Edit Division Superintendent" => "")
        ];

        $user = User::find($division_superintendent->user_id);
        $person = Person::find($user->person_id);
        switch (Auth::user()->classification) {
            case 'Division Administrator':
                $divisions = Division::where('id', $division_superintendent->division_id)->get();
                $attribute = "disabled";
                break;
            case 'System Administrator':
                $divisions = Division::all();
                $attribute = "";
                break;
            default:
                break;
        }
        return view('division_superintendents.edit', compact(
            'page', 'user', 'person', 
            'divisions', 'division_superintendent', 'attribute'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DivisionSuperIntendent  $divisionSuperIntendent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();
        
        try {
            
            $division_superintendent = DivisionSuperIntendent::find($request->id);
            $user = User::find($division_superintendent->user_id);
            $person = Person::find($user->person_id);
            
            if (Auth::user()->classification == "System Administrator") {
                $division_superintendent->division_id = $request->division;
                $division_superintendent->save();
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
            return back()->with('success', 'Division Superintendent has been updated successfully.');
        } else {
            return back()->withErrors($result);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DivisionSuperIntendent  $divisionSuperIntendent
     * @return \Illuminate\Http\Response
     */
    public function destroy(DivisionSuperIntendent $divisionSuperIntendent)
    {
        //
    }
}
