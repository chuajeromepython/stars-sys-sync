<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = [
            'name'      =>  'Division',
            'title'     =>  'Division Management',
            'crumb'     =>  array('Division' => '/divisions')
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
        $this->validate($request,[
            'name' => 'required'
        ]);

        $check_division = Division::where('name', '=', $request->name)->get();

        if(!count($check_division)) {
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
     * @param  \App\Models\Division  $division
     * @return \Illuminate\Http\Response
     */
    public function show(Division $division)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Division  $division
     * @return \Illuminate\Http\Response
     */
    public function edit(Division $division)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Division  $division
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $existing = Division::where('name', $request->name)
            ->where('id', '<>', $request->id)->get();

        if($existing->count() == 0){
            $division = Division::find($request->id);
            $division->name = $request->name;
            $division->save();
            return back()->with('success', 'Division has been updated successfully.');
        }else{
            return back()->withErrors('Division already exists!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Division  $division
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request)
    {
        $division = Division::find($request->id);
        $division->delete();
        return back()->with("success", "Division has been successfully deleted.");
       
    }
}
