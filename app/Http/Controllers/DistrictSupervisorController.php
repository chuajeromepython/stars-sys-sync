<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Auth;
use App\Models\DistrictSupervisor;
use App\Models\DivisionAdministrator;
use App\Models\District;
use App\Models\User;
use App\Models\Person;

class DistrictSupervisorController extends Controller
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
     * @param  \App\Models\DistrictSupervisor  $districtSupervisor
     * @return \Illuminate\Http\Response
     */
    public function show(DistrictSupervisor $districtSupervisor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DistrictSupervisor  $districtSupervisor
     * @return \Illuminate\Http\Response
     */
    public function edit(DistrictSupervisor $districtSupervisor)
    {
        $page = [
            'name'      =>  'User',
            'title'     =>  'Edit District Supervisor',
            'crumb'     =>  array('Users' => '/users', "Edit District Supervisor" => "")
        ];

        switch (Auth::user()->classification) {
            case 'Division Administrator':
                $division_id = DivisionAdministrator::where('user_id', Auth::user()->id)->value('division_id');
                $districts = District::select(
                    'tbl_districts.id as id',
                    'tbl_districts.name as district',
                    'tbl_divisions.name as division',
                )->join('tbl_divisions', 'tbl_districts.division_id', 'tbl_divisions.id')
                ->where('division_id', $division_id)->get();
                break;
            case 'System Administrator':
                $districts = District::select(
                    'tbl_districts.id as id',
                    'tbl_districts.name as district',
                    'tbl_divisions.name as division',
                )->join('tbl_divisions', 'tbl_districts.division_id', 'tbl_divisions.id')
                ->get();
                break;
            default:
                break;
        }

        
        $user = User::find($districtSupervisor->user_id);
        $person = Person::find($user->person_id);

        return view('district_supervisors.edit', compact(
            'page', 'user', 'person', 
            'districts', 'districtSupervisor'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DistrictSupervisor  $districtSupervisor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();
        
        try {
            
            $district_supervisor = DistrictSupervisor::find($request->id);
            $user = User::find($district_supervisor->user_id);
            $person = Person::find($user->person_id);
            
            $district_supervisor->district_id = $request->district_id;
            $district_supervisor->save();

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
            return back()->with('success', 'District Supervisor has been updated successfully.');
        } else {
            return back()->withErrors($result);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DistrictSupervisor  $districtSupervisor
     * @return \Illuminate\Http\Response
     */
    public function destroy(DistrictSupervisor $districtSupervisor)
    {
        //
    }
}
