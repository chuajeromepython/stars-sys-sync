<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Strand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Course',
            'title' => 'Course Management',
            'crumb' => ['Course' => '/Courses'],
        ];

        $courses = Course::select(
            'course', 'strand_id',
            'tbl_courses.id as id', 'tbl_strands.name as strand'
        )->join('tbl_strands', 'tbl_courses.strand_id', 'tbl_strands.id')
            ->get();

        $strands = Strand::all();

        return view('courses.index', compact(
            'page',
            'courses',
            'strands'
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
        $request->validate([
            'strand' => 'required',
            'course' => 'required',
        ]);

        $result = Course::where('course', '=', $request->course)
            ->where('strand_id', '=', $request->strand)
            ->get();

        if (! count($result)) {
            $course = new Course;
            $course->course = $request->course;
            $course->strand_id = $request->strand;
            $course->save();

            return redirect('/courses')->with('success', 'New course has been added successfully.');
        } else {
            return back()->withErrors('Course already exists!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Course $course)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Course $course)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Course  $course
     * @return Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'strand_id' => 'required',
            'course' => 'required',
        ]);

        $result = Course::where('course', '=', $request->course)
            ->where('strand_id', '=', $request->strand_id)
            ->where('id', '<>', $request->id)
            ->get();

        if (! count($result)) {
            $course = Course::find($request->id);
            $course->course = $request->course;
            $course->strand_id = $request->strand_id;
            $course->save();

            return redirect('/courses')->with('success', 'Course has been updated successfully.');
        } else {
            return back()->withErrors('Course already exists!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Course  $course
     * @return Response
     */
    public function destroy(Request $request)
    {
        $course = Course::find($request->id);
        $course->delete();

        return back()->with('success', 'Course has been deleted successfully.');
    }
}
