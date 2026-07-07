<?php

namespace App\Http\Controllers;

use App\Models\SubjectComponent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubjectComponentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {}

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
            'subject_id' => 'required',
            'name' => 'required',
        ]);

        $result = SubjectComponent::where('subject_id', '=', $request->subject_id)
            ->where('name', '=', $request->name)
            ->get();

        if (! count($result)) {

            $subject = new SubjectComponent;
            $subject->subject_id = $request->subject_id;
            $subject->name = $request->name;
            $subject->save();

            return back()->with('success', 'New subject component has been added successfully.');

        } else {
            return back()->withErrors('Subject component already exists!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(SubjectComponent $subjectComponent, Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'name' => 'required',
        ]);

        $result = SubjectComponent::where('subject_id', '=', $request->subject_id)
            ->where('name', '=', $request->name)
            ->get();

        if (! count($result)) {

            $subject = new SubjectComponent;
            $subject->subject_id = $request->subject_id;
            $subject->name = $request->name;
            $subject->save();

            return back()->with('success', 'New subject component has been added successfully.');

        } else {
            return back()->withErrors('Subject component already exists!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(SubjectComponent $subjectComponent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  SubjectComponent  $subjectComponent
     * @return Response
     */
    public function update(Request $request)
    {
        $existing = SubjectComponent::where('name', $request->name)
            ->where('subject_id', $request->subject_id)
            ->where('id', '<>', $request->id)
            ->get();

        if ($existing->count() == 0) {

            $component = SubjectComponent::find($request->id);
            $component->name = $request->name;
            $component->subject_id = $request->subject_id;
            $component->save();

            return back()->with('success', 'Subject Component has been updated successfully.');

        } else {
            return back()->withErrors('Subject Component already exists!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  SubjectComponent  $subjectComponent
     * @return Response
     */
    public function destroy(Request $request)
    {
        $subject = SubjectComponent::find($request->id);
        $subject->delete();

        return back()->with('success', 'Subject Component has been deleted successfully.');
    }
}
