<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectComponent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $page = [
            'name' => 'Subject',
            'title' => 'Subject Management',
            'crumb' => ['Subject' => '/subjects'],
        ];
        $subjects = Subject::all();

        return view('subjects.index', compact(
            'page',
            'subjects',
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
            'title' => 'required',
        ]);

        $result = Subject::where('title', '=', $request->title)->get();

        if (! count($result)) {
            $subject = new Subject;
            $subject->title = $request->title;
            $subject->save();

            return redirect('/subjects')->with('success', 'New subject has been added successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Subject already exists!']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Subject $subject)
    {
        $page = [
            'name' => 'Subject',
            'title' => 'Subject Components Management',
            'crumb' => ['Subject' => '/subjects'],
        ];

        $subjects = Subject::all();
        $components = SubjectComponent::where('subject_id', $subject->id)->get();

        return view('subjects.show', compact(
            'page',
            'components', 'subjects', 'subject'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Subject $subject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Subject  $subject
     * @return Response
     */
    public function update(Request $request)
    {
        $existing = Subject::where('title', $request->title)
            ->where('id', '<>', $request->id)->get();

        if ($existing->count() == 0) {
            $dsubject = Subject::find($request->id);
            $dsubject->title = $request->title;
            $dsubject->save();

            return back()->with('success', 'Subject has been updated successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Subject already exists!']);
        }
    }

    public function destroy(Request $request)
    {
        $subject = Subject::find($request->id);
        $subject->delete();

        return back()->with('success', 'Subject has been deleted successfully.');
    }
}
