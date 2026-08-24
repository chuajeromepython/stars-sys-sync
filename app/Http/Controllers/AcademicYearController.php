<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Academic Year',
            'title' => 'Academic Year Management',
            'crumb' => ['Academic Year' => '/academic_years'],
        ];

        $academic_years = AcademicYear::all();

        return view('academic_years.index', compact(
            'page',
            'academic_years',
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
            'year_from' => 'required|numeric',
            'year_to' => 'required|numeric',
        ]);

        $result = AcademicYear::where([
            ['from', '=', $request->year_from],
            ['to', '=', $request->year_to],
        ])->get();

        if (!count($result)) {

            $academicYear = new AcademicYear;
            $academicYear->from = $request->year_from;
            $academicYear->to = $request->year_to;
            $academicYear->is_active = false;
            $academicYear->save();

            return redirect('/academic_years')->with('success', 'New academic year has been added successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Academic year already exists!']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(AcademicYear $academicYear)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(AcademicYear $academicYear)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  AcademicYear  $academicYear
     * @return Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'year_from' => 'required|numeric',
            'year_to' => 'required|numeric',
        ]);

        $result = AcademicYear::where([
            ['id', '<>', $request->id],
            ['from', '=', $request->year_from],
            ['to', '=', $request->year_to],
        ])->get();

        if (!count($result)) {

            $academicYear = AcademicYear::find($request->id);
            $academicYear->from = $request->year_from;
            $academicYear->to = $request->year_to;
            $academicYear->is_active = $request->is_active;
            $academicYear->save();

            return redirect('/academic_years')->with('success', 'Academic Year has been updated successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Academic Year already exists!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  AcademicYear  $academicYear
     * @return Response
     */
    public function destroy(Request $request)
    {
        $academicYear = AcademicYear::find($request->id);
        $academicYear->delete();

        return redirect('/academic_years')->with('success', 'Academic Year has been deleted successfully.');
    }
}
