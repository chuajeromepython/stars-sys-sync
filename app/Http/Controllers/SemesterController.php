<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SemesterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Semester',
            'title' => 'Semester Management',
            'crumb' => ['Semester' => '/divisions'],
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
            'semester' => 'required',
        ]);

        $result = Semester::where('semester', '=', $request->semester)->get();

        if (! count($result)) {
            $semester = new Semester;
            $semester->semester = $request->semester;
            $semester->save();

            return redirect('/semesters')->with('success', 'New semester has been added successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Semester already exists!']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Semester $semester)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Semester $semester)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Semester  $semester
     * @return Response
     */
    public function update(Request $request)
    {
        $existing = Semester::where('semester', $request->semester)
            ->where('id', '<>', $request->id)->get();

        if ($existing->count() == 0) {
            $semester = Semester::find($request->id);
            $semester->semester = $request->semester;
            $semester->save();

            return back()->with('success', 'Semester has been updated successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Semester already exists!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Semester  $semester
     * @return Response
     */
    public function destroy(Request $request)
    {
        $semester = Semester::find($request->id);
        $semester->delete();

        return back()->with('success', 'Semester has been deleted successfully.');
    }
}
