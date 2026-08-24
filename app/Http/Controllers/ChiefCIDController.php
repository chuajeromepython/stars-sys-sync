<?php

namespace App\Http\Controllers;

use App\Models\ChiefCID;
use App\Models\Division;
use App\Models\Person;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ChiefCIDController extends Controller
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
    public function show(ChiefCID $chiefCID)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ChiefCID  $chiefCID
     * @return Response
     */
    public function edit(ChiefCID $chief_cid)
    {
        $page = [
            'name' => 'User',
            'title' => 'Edit Chief CID',
            'crumb' => ['Users' => '/users', 'Edit Chief CID' => ''],
        ];

        $user = User::find($chief_cid->user_id);
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

        return view('chief_cids.edit', compact(
            'page', 'user', 'person',
            'divisions', 'chief_cid', 'attribute'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ChiefCID  $chiefCID
     * @return Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();

        try {

            $chief_cid = ChiefCID::find($request->id);
            $user = User::find($chief_cid->user_id);
            $person = Person::find($user->person_id);

            if (Auth::user()->classification == 'System Administrator') {
                $chief_cid->division_id = $request->division;
                $chief_cid->save();
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
            return back()->with('success', 'Chief CID has been updated successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => $result]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(ChiefCID $chiefCID)
    {
        //
    }
}
