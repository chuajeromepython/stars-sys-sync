<?php

namespace App\Http\Controllers;

use App\Http\Traits\CompetencyTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Competency;
use App\Models\Subject;
use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\Week;
use PhpOffice\PhpWord\Shared\ZipArchive;

class CompetencyController extends Controller
{
    use CompetencyTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = [
            'name'      =>  'Competency',
            'title'     =>  'Competency Management',
            'crumb'     =>  array('Competency' => '/competencies')
        ];

        $competencies = Competency::select(
            'tbl_competencies.id as id', 
            'tbl_competencies.*',
            'tbl_subjects.title', 'tbl_grade_levels.level'
        )->join('tbl_subjects', 'tbl_competencies.subject_id', 'tbl_subjects.id')
        ->join('tbl_grade_levels', 'tbl_competencies.grade_level_id', 'tbl_grade_levels.id')
        ->get();



        return view('competencies.index', compact(
            'page', 
            'competencies',
        ));
    }

    public function datatable(){

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page = [
            'name'      =>  'Competency',
            'title'     =>  'Add Competency',
            'crumb'     =>  array(
                'Competency' => '/competencies', 
                'Add Competency' => '/competencies/create'
            )
        ];

        $subjects = Subject::all();
        $grade_levels = GradeLevel::all();
        $periods = Period::all();
        $weeks = Week::all();

        return view('competencies.create', compact(
            'page',
            'subjects',
            'grade_levels',
            'periods',
            'weeks'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'code' => 'required',
            'description' => 'required',
            'grade_level' => 'required',
            'subject' => 'required'
        ]);


        // dd($request);
        $result = Competency::where([
            ['code', '=', $request->code]
        ])->get();

        if($result->count() == 0) {

            $competency = new Competency;
            $competency->code = $request->code;
            $competency->description = $request->description;
            $competency->grade_level_id = $request->grade_level;
            $competency->subject_id = $request->subject;
            $competency->week_id = $request->week;
            $competency->period_id = $request->period;
            $competency->save();

            return redirect('/competencies/create')->with('success', 'New Competency has been added successfully.');

        } else {

            return back()->withErrors( $request->code.' competency already exists !' );
        
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Competency  $competency
     * @return \Illuminate\Http\Response
     */
    public function show(Competency $competency)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Competency  $competency
     * @return \Illuminate\Http\Response
     */
    public function edit(Competency $competency)
    {
        $page = [
            'name'      =>  'Competency',
            'title'     =>  'Edit Competency',
            'crumb'     =>  array(
                'Competency' => '/competencies', 
                'Edit Competency' => ''
            )
        ];

        $subjects = Subject::all();
        $grade_levels = GradeLevel::all();
        $periods = Period::all();
        $weeks = Week::all();


        return view('competencies.edit', compact(
            'page',
            'subjects',
            'grade_levels', 'competency',
            'periods', 'weeks'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Competency  $competency
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request,[
            'code' => 'required',
            'description' => 'required',
            'grade_level' => 'required',
            'subject' => 'required'
        ]);


        // dd($request);
        $result = Competency::where([
            ['code', '=', $request->code],
            ['id', '<>', $request->id]
        ])->get();

        if($result->count() == 0) {

            $competency = Competency::find($request->id);
            $competency->code = $request->code;
            $competency->description = $request->description;
            $competency->grade_level_id = $request->grade_level;
            $competency->subject_id = $request->subject;
            $competency->week_id = $request->week;
            $competency->period_id = $request->period;
            $competency->save();

            return redirect('/competencies')->with('success', 'Competency has been updated successfully.');

        } else {

            return back()->withErrors( $request->code.' competency already exists !' );
        
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Competency  $competency
     * @return \Illuminate\Http\Response
     */
    public function destroy(Competency $competency)
    {
        //
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240'
        ], [
            'file.required' => 'Please upload a file.',
            'file.mimes' => 'The file must be an Excel file (xlsx or xls).',
            'file.max' => 'The file size must not exceed 10MB.'
        ]);

        $spreadsheet = IOFactory::load( $request->file('file') );
        $sheet = $spreadsheet->getActiveSheet()->toArray();
        $errors = array();
        $error_messages = array();

        DB::beginTransaction();
        try {

            foreach ($sheet as $key => $row) {
                if ($key > 1) {
                    if ($row[0] == null) { break; }
                    
                    $line = $key-1;
                    $error = array();
                    $subject = Subject::where('title', $row[2])->first();
                    
                    if(is_null($subject)) {
                        return redirect('/competencies')->withErrors('Subject not found!');
                    }
                    
                    if($subject->subject_components->count() < 1) {
                        $this->uploadSubject($errors, $row, $line);
                    }
                    else {
                        $this->uploadWithSubjectComponent($errors, $row, $line);
                    }
                }
            }

            if(sizeof($errors) > 0){
                foreach($errors as $error_msgs){
                    foreach ($error_msgs as $key => $message) {
                        $error_messages[] = $message;
                    }
                }
                return back()->withErrors($error_messages);
            }

            DB::commit();
            $result = true;
            
        } catch (Exception $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if($result === true) {
            return redirect('/competencies')->with('success', 'Competency Uploader has been uploaded successfully.');
        } else {
            return back()->withErrors($result);
        }

    }


}
