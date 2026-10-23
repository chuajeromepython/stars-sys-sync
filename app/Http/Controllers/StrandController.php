<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Strand;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Strand',
            'title' => 'Strand Management',
            'crumb' => ['Strand' => '/strands'],
        ];

        $strands = Strand::select(
            'tbl_strands.id as id',
            'tbl_strands.name as strand',
            'tbl_strands.track_id',
            'tbl_tracks.name as track',
        )->join('tbl_tracks', 'tbl_strands.track_id', 'tbl_tracks.id')
            ->get();

        $tracks = Track::all();

        return view('strands.index', compact(
            'page',
            'strands',
            'tracks'
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
            'track' => 'required',
            'name' => 'required',
        ]);

        $result = Strand::where('name', '=', $request->name)
            ->where('track_id', '=', $request->track)
            ->get();

        if (! count($result)) {
            $strand = new Strand;
            $strand->name = $request->name;
            $strand->track_id = $request->track;
            $strand->save();

            return redirect('/strands')->with('success', 'New strand has been added successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Strand already exists!']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Strand $strand) {}

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Strand $strand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Strand  $strand
     * @return Response
     */
    public function update(Request $request)
    {

        $request->validate([
            'track_id' => 'required',
            'name' => 'required',
        ]);

        $result = Strand::where('name', '=', $request->name)
            ->where('track_id', '=', $request->track_id)
            ->where('id', '<>', $request->id)
            ->get();

        if (! count($result)) {
            $strand = Strand::find($request->id);
            $strand->name = $request->name;
            $strand->track_id = $request->track_id;
            $strand->save();

            return redirect('/strands')->with('success', 'Strand has been updated successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => 'Strand already exists!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Strand  $strand
     * @return Response
     */
    public function destroy(Request $request)
    {
        $courses = Course::where('strand_id', $request->id)->get();
        $strand = Strand::find($request->id);
        if ($courses->count() == 0) {
            $strand->delete();

            return back()->with('success', 'Strand has been deleted successfully.');
        } else {
            $errors[] = 'There are data found under '.$strand->name.' :';
            $errors[] = ($courses->count() == 1)
                ? $courses->count().' active Course found.'
                : $courses->count().' active Courses found.';

            return redirect()->to(url()->previous())->withErrors(['error' => $errors]);
        }

    }
}
