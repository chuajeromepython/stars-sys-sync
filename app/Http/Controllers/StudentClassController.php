<?php

namespace App\Http\Controllers;

use App\Models\StudentClass;
use App\Models\StudentClassroom;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StudentClassController extends Controller
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
    public function show(StudentClass $studentClass)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(StudentClass $studentClass)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, StudentClass $studentClass)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
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

                $existing = StudentClass::where([
                    'student_id' => $student->student_id,
                    'class_id' => $request->class_id,
                ])->first();

                if (! $existing) {
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

        if ($result === true) {
            return back()->with('success', $student_count.'/'.$students->count().' Students has been synced successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => $result]);
        }
    }
}
