<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Division',
            'title' => 'Division Management',
            'crumb' => ['Division' => '/divisions'],
        ];

        $divisions = Division::all();

        return view('divisions.index', compact(
            'page',
            'divisions',
        ));
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
        $request->validate($request, [
            'name' => 'required',
        ]);

        $check_division = Division::where('name', '=', $request->name)->get();

        if (! count($check_division)) {
            $division = new Division;
            $division->name = $request->name;
            $division->save();

            return back()->with('success', 'New division has been added successfully.');
        } else {
            return back()->withErrors('Division already exists!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Division $division) {}

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Division $division)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Division  $division
     * @return Response
     */
    public function update(Request $request)
    {
        $existing = Division::where('name', $request->name)
            ->where('id', '<>', $request->id)->get();

        if ($existing->count() == 0) {
            $division = Division::find($request->id);
            $division->name = $request->name;
            $division->save();

            return back()->with('success', 'Division has been updated successfully.');
        } else {
            return back()->withErrors('Division already exists!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Division  $division
     * @return Response
     */
    public function destroy(Request $request)
    {
        $division = Division::find($request->id);
        $division->delete();

        return back()->with('success', 'Division has been successfully deleted.');

    }
}
