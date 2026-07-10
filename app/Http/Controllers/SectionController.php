<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\SchoolSupervisor;
use App\Models\Section;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Section',
            'title' => 'Section Management',
            'crumb' => ['Section' => '/divisions'],
        ];
        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $sections = Section::where('school_id', $school_id)->get();

        return view('sections.index', compact(
            'page',
            'sections',
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
            'section' => 'required',
        ]);

        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $result = Section::where('section', '=', strtoupper($request->section))
            ->where('school_id', $school_id)->get();

        if (! count($result)) {
            $section = new Section;
            $section->section = strtoupper($request->section);
            $section->school_id = $school_id;
            $section->save();

            return redirect('/sections')->with('success', 'New section has been added successfully.');
        } else {
            return back()->withErrors('Section already exists!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Section  $section
     * @return Response
     */
    public function update(Request $request)
    {

        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $existing = Section::where('section', $request->section)
            ->where('school_id', $school_id)
            ->where('id', '<>', $request->id)
            ->get();

        if ($existing->count() == 0) {
            $section = Section::find($request->id);
            $section->section = $request->section;
            $section->save();

            return back()->with('success', 'Section has been updated successfully.');
        } else {
            return back()->withErrors('Section already exists!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Section  $section
     * @return Response
     */
    public function destroy(Request $request)
    {

        $section = Section::find($request->id);
        $has_record = Classroom::where('section_id', $request->id)->get();

        if ($has_record) {
            return back()->withErrors('Section "'.$section->section.'" has Classroom Record');
        } else {

            $section->delete();

            return back()->with('success', 'Section has been deleted successfully.');
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'Please upload a file.',
            'file.mimes' => 'The file must be an Excel file (xlsx or xls).',
            'file.max' => 'The file size must not exceed 10MB.',
        ]);

        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $spreadsheet = IOFactory::load($request->file('file'));
        $sheet = $spreadsheet->getActiveSheet()->toArray();
        $errors = [];
        $sheet = array_unique($sheet, SORT_REGULAR);

        DB::beginTransaction();
        try {

            foreach ($sheet as $key => $row) {
                if ($key > 1) {
                    $line = $key - 1;
                    $is_existing = Section::where('section', strtoupper($row[0]))
                        ->where('school_id', $school_id)->get();

                    if ($row[0] == null) {
                        break;
                    }

                    if ($is_existing->count() > 0) {
                        $errors[] = 'Error on row '.$line.' : Duplicate section '.$row[0].'. ';
                    } else {
                        $section = new Section;
                        $section->section = strtoupper($row[0]);
                        $section->school_id = $school_id;
                        $section->save();
                    }

                }
            }

            if (count($errors) > 0) {
                return back()->withErrors($errors);
            }

            DB::commit();
            $result = true;

        } catch (Exception $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if ($result === true) {
            return redirect('/sections')->with('success', 'Section Uploader has been uploaded successfully.');
        } else {
            return back()->withErrors($result);
        }

    }
}
