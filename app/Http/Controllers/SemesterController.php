<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = [
            'name'      =>  'Semester',
            'title'     =>  'Semester Management',
            'crumb'     =>  array('Semester' => '/divisions')
        ];

        $semesters = Semester::all();
        return view('semesters.index', compact(
            'page', 
            'semesters',
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
            'semester' => 'required'
        ]);

        $result = Semester::where('semester', '=', $request->semester)->get();

        if(!count($result)) {
            $semester = new semester;
            $semester->semester = $request->semester;
            $semester->save();
            return redirect('/semesters')->with('success', 'New semester has been added successfully.');
        } else {
            return back()->withErrors('Semester already exists!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Semester  $semester
     * @return \Illuminate\Http\Response
     */
    public function show(Semester $semester)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Semester  $semester
     * @return \Illuminate\Http\Response
     */
    public function edit(Semester $semester)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Semester  $semester
     * @return \Illuminate\Http\Response
     */
     public function update(Request $request)
    {
         $existing = Semester::where('semester', $request->semester)
            ->where('id', '<>', $request->id)->get();

        if($existing->count() == 0){
            $semester = Semester::find($request->id);
            $semester->semester = $request->semester;
            $semester->save();
            return back()->with('success', 'Semester has been updated successfully.');
        }else{
            return back()->withErrors('Semester already exists!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Semester  $semester
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $semester = Semester::find($request->id);
        $semester->delete();
        return back()->with('success', 'Semester has been deleted successfully.');
    }
}
