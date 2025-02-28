<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\StudentClass;
use App\Models\StudentClassroom;

class StudentClassController extends Controller
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
     * @param  \App\Models\StudentClass  $studentClass
     * @return \Illuminate\Http\Response
     */
    public function show(StudentClass $studentClass)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudentClass  $studentClass
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentClass $studentClass)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StudentClass  $studentClass
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentClass $studentClass)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudentClass  $studentClass
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentClass $studentClass)
    {
        //
    }

    public function sync(Request $request)
    {   
        
        DB::beginTransaction();
        try {

            $students = StudentClassroom::where('classroom_id', $request->classroom_id)->get();
            $student_count = 0;
            foreach ($students as $key => $student) {

                $existing =  StudentClass::where([
                    "student_id" => $student->student_id,
                    "class_id" => $request->class_id,
                ])->first();

                if (!$existing){
                    $student_class = new StudentClass;
                    $student_class->student_id = $student->student_id;
                    $student_class->class_id = $request->class_id;
                    $student_class->status = $student->status;
                    $student_class->save(); 
                    $student_count++;
                }
                
            }


            DB::commit();
            $result = true;
            
        } catch (Exception $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if($result === true) {
            return back()->with('success', $student_count."/".$students->count().' Students has been synced successfully.');
        } else {
            return back()->withErrors($result);
        }


    }
}
