<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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

class ClassificationHistoryController extends Controller
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
     * @param  \App\Models\ClassificationHistory  $classificationHistory
     * @return \Illuminate\Http\Response
     */
    public function show(ClassificationHistory $classificationHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClassificationHistory  $classificationHistory
     * @return \Illuminate\Http\Response
     */
    public function edit(ClassificationHistory $classificationHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ClassificationHistory  $classificationHistory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {   

        DB::beginTransaction();
        try {
   
            if ($request->district){

                $existing = DistrictSupervisor::where('user_id', $user->id)->get();
                $supervisor = ($existing->count() == 0) 
                    ? new DistrictSupervisor 
                    : DistrictSupervisor::find($existing[0]->id);

                $supervisor->status = true;
                $supervisor->user_id = $user->id;
                $supervisor->district_id = $request->district;
                $supervisor->email =  $user->username;
                $supervisor->save();
            } 
            if ($request->school_id) {
                if ($request->classification == "School Supervisor") {
                    $existing = SchoolSupervisor::where('user_id', $user->id)->get();
                    $supervisor = ($existing->count() == 0) 
                        ? new SchoolSupervisor 
                        : SchoolSupervisor::find($existing[0]->id);
                    $supervisor->status = true;
                    $supervisor->user_id = $user->id;
                    $supervisor->school_id = $request->school_id;
                    $supervisor->email =  $user->username;
                    $supervisor->save();
                }elseif ($request->classification == "Department Head") {
                    $existing = DepartmentHead::where('user_id', $user->id)->get();
                    $supervisor = ($existing->count() == 0) 
                        ? new DepartmentHead 
                        : DepartmentHead::find($existing[0]->id);
                    $supervisor->subject_id = json_encode($request->subjects);
                    $supervisor->user_id = $user->id;
                    $supervisor->school_id = $request->school_id;
                    $supervisor->email =  $user->username;
                    $supervisor->save();
                }elseif ($request->classification == "Teacher") {
                    $existing = Teacher::where('user_id', $user->id)->get();
                    $supervisor = ($existing->count() == 0) 
                        ? new Teacher 
                        : Teacher::find($existing[0]->id);
                    $supervisor->user_id = $user->id;
                    $supervisor->school_id = $request->school_id;
                    $supervisor->email =  $user->username;
                    $supervisor->save();
                }
            }
            if ($request->division) {
                if ($request->classification == "Division Supervisor") {
                    $existing = DivisionSupervisor::where('user_id', $user->id)->get();
                    $supervisor = ($existing->count() == 0) 
                        ? new DivisionSupervisor 
                        : DivisionSupervisor::find($existing[0]->id);
                    $supervisor->subject_id = json_encode($request->subjects);
                } elseif ($request->classification == "Division Superintendent") {
                    $existing = DivisionSuperIntendent::where('user_id', $user->id)->get();
                    $supervisor = ($existing->count() == 0) 
                        ? new DivisionSuperIntendent 
                        : DivisionSuperIntendent::find($existing[0]->id);
                } elseif ($request->classification == "Assistant Division Superintendent") {
                    $existing = AssistantDivisionSuperIntendent::where('user_id', $user->id)->get();
                    $supervisor = ($existing->count() == 0) 
                        ? new AssistantDivisionSuperIntendent 
                        : AssistantDivisionSuperIntendent::find($existing[0]->id);
                } elseif ($request->classification == "Chief of CID") {
                    $existing = ChiefCID::where('user_id', $user->id)->get();
                    $supervisor = ($existing->count() == 0) 
                        ? new ChiefCID 
                        : ChiefCID::find($existing[0]->id);
                } elseif ($request->classification == "Chief of SGOD") {
                    $existing = ChiefSGOD::where('user_id', $user->id)->get();
                    $supervisor = ($existing->count() == 0) 
                        ? new ChiefSGOD 
                        : ChiefSGOD::find($existing[0]->id);
                }

                $supervisor->status = true;
                $supervisor->user_id = $user->id;
                $supervisor->division_id = $request->division;
                $supervisor->email =  $user->username;
                $supervisor->save();
            }

            $user->classification = $request->classification;
            $user->save();

            $history = new ClassificationHistory; 
            $history->user_id = $user->id;
            $history->classification = $request->classification;
            $history->encoder_user_id = Auth::user()->id;
            $history->save();

            DB::commit();
            $result = true;

        } catch (Exception $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if($result === true) {
            return back()->with('success', 'User Classification has been added successfully.');
        } else {
            return back()->withErrors($result);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClassificationHistory  $classificationHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClassificationHistory $classificationHistory)
    {
        //
    }
}
