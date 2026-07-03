<?php

namespace App\Http\Controllers;

use App\Models\ChiefSGOD;
use App\Models\Division;
use App\Models\Person;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ChiefSGODController extends Controller
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

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(ChiefSGOD $chiefSGOD)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ChiefSGOD  $chiefSGOD
     * @return Response
     */
    public function edit(ChiefSGOD $chief_sgod)
    {
        $page = [
            'name' => 'User',
            'title' => 'Edit Chief SGOD',
            'crumb' => ['Users' => '/users', 'Edit Chief SGOD' => ''],
        ];

        $user = User::find($chief_sgod->user_id);
        $person = Person::find($user->person_id);
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

        return view('chief_sgods.edit', compact(
            'page', 'user', 'person',
            'divisions', 'chief_sgod', 'attribute'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ChiefSGOD  $chiefSGOD
     * @return Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();

        try {

            $chief_sgod = ChiefSGOD::find($request->id);
            $user = User::find($chief_sgod->user_id);
            $person = Person::find($user->person_id);

            if (Auth::user()->classification == 'System Administrator') {
                $chief_sgod->division_id = $request->division;
                $chief_sgod->save();
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

        if ($result === true) {
            return back()->with('success', 'Chief SGOD has been updated successfully.');
        } else {
            return back()->withErrors($result);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(ChiefSGOD $chiefSGOD)
    {
        //
    }
}
