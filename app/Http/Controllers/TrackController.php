<?php

namespace App\Http\Controllers;

use App\Models\Strand;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Track',
            'title' => 'Track Management',
            'crumb' => ['Track' => '/tracks'],
        ];

        $tracks = Track::all();

        return view('tracks.index', compact(
            'page',
            'tracks',
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
            'name' => 'required',
        ]);

        $result = Track::where('name', '=', $request->name)->get();

        if (! count($result)) {
            $track = new Track;
            $track->name = $request->name;
            $track->save();

            return redirect('/tracks')->with('success', 'New track has been added successfully.');
        } else {
            return back()->withErrors('Track already exists!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Track $track)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Track $track)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Track  $track
     * @return Response
     */
    public function update(Request $request)
    {
        $existing = Track::where('name', $request->name)
            ->where('id', '<>', $request->id)->get();

        if ($existing->count() == 0) {
            $track = Track::find($request->id);
            $track->name = $request->name;
            $track->save();

            return back()->with('success', 'Track has been updated successfully.');
        } else {
            return back()->withErrors('Semester already exists!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Track  $track
     * @return Response
     */
    public function destroy(Request $request)
    {
        $strands = Strand::where('track_id', $request->id)->get();
        $track = Track::find($request->id);
        if ($strands->count() == 0) {
            $track->delete();

            return back()->with('success', 'Track has been deleted successfully.');
        } else {
            $errors[] = 'There are data found under '.$track->name.' :';
            $errors[] = ($strands->count() == 1)
                ? $strands->count().' active Strand found.'
                : $strands->count().' active Strands found.';

            return back()->withErrors($errors);
        }
    }
}
