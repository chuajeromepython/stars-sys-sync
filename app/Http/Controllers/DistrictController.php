<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Division;
use App\Models\DivisionAdministrator;
use App\Models\School;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'District',
            'title' => 'District Management',
            'crumb' => ['District' => '/tracks'],
        ];

        // $tracks = Track::all();
        $division_id = DivisionAdministrator::where('user_id', Auth::user()->id)->value('division_id');
        $districts = District::where('division_id', $division_id)->get();
        $divisions = Division::all();

        return view('districts.index', compact(
            'page',
            'districts', 'divisions', 'division_id'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);
        $division_id = DivisionAdministrator::where('user_id', Auth::user()->id)->value('division_id');
        $result = District::where([
            ['name', '=', $request->name],
            ['division_id', '=', $division_id],
        ])->get();

        if ($result->count() == 0) {

            $district = new District;
            $district->name = $request->name;
            $district->division_id = $division_id;
            $district->save();

            return redirect('/districts')->with('success', 'New district has been added successfully.');

        } else {
            return back()->withErrors('District already exists!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(District $district) {}

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(District $district)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  District  $district
     * @return Response
     */
    public function update(Request $request)
    {
        $existing = District::where('name', $request->name)
            ->where('division_id', $request->division_id)
            ->where('id', '<>', $request->id)->get();

        if ($existing->count() == 0) {
            $district = District::find($request->id);
            $district->name = $request->name;
            $district->division_id = $request->division_id;
            $district->save();

            return back()->with('success', 'District has been updated successfully.');
        } else {
            return back()->withErrors('District already exists!');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  District  $district
     * @return Response
     */
    public function destroy(Request $request)
    {

        $school = School::where('district_id', $request->id)->get();
        $district = District::find($request->id);

        if ($school->count() == 0) {
            $district->delete();

            return back()->with('success', 'District has been deleted successfully.');
        } else {
            $noun = ($school->count() == 1) ? 'school' : 'schools';
            $message = $school->count().' '.$noun.' '.'found under '.$district->name.' district.';

            return back()->withErrors($message);
        }

    }
}
