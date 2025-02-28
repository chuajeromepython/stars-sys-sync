<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use Illuminate\Http\Request;

class GradeLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = [
            'name'      =>  'Grade Level',
            'title'     =>  'Grade Level Management',
            'crumb'     =>  array('Grade Level' => '/grade_levels')
        ];

        $grade_levels = GradeLevel::all();
        return view('grade_levels.index', compact(
            'page', 
            'grade_levels',
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
            'level' => 'required'
        ]);

        $result = GradeLevel::where([
            ['level', '=', $request->level]
        ])->get();

        if(!count($result)) {

            $gradeLevel = new GradeLevel;
            $gradeLevel->level = $request->level;
            $gradeLevel->save();

            return back()->with('success', 'New grade level has been added successfully.');      
        } else {
            return back()->withErrors('Grade Level already exists!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GradeLevel  $gradeLevel
     * @return \Illuminate\Http\Response
     */
    public function show(GradeLevel $gradeLevel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GradeLevel  $gradeLevel
     * @return \Illuminate\Http\Response
     */
    public function edit(GradeLevel $gradeLevel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GradeLevel  $gradeLevel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $existing = GradeLevel::where('level', $request->level)
            ->where('id', '<>', $request->id)->get();

        if($existing->count() == 0){
            $grade_level = GradeLevel::find($request->id);
            $grade_level->level = $request->level;
            $grade_level->save();
            return back()->with('success', 'Grade Level has been updated successfully.');
        }else{
            return back()->withErrors('Grade Level already exists!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GradeLevel  $grade_level
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $gradeLevel = GradeLevel::find($request->id);
        $gradeLevel->delete();
        return back()->with('success', 'Grade Level has been deleted successfully.');
    }
}
