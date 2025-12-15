<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Auth;
use App\Models\Assessment;
use App\Models\AssessmentKey;
use App\Models\AssessmentOption;
use App\Models\AssessmentClass;
use App\Models\ClassAssessment;
use App\Models\CustomFunction;
use App\Models\Question;
use App\Models\Teacher;
use App\Models\TeacherClass;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\StudentClass;
use App\Models\StudentAnswer;

class StudentAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StudentAnswer  $studentAnswer
     * @return \Illuminate\Http\Response
     */
    public function show(StudentAnswer $studentAnswer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudentAnswer  $studentAnswer
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentAnswer $studentAnswer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StudentAnswer  $studentAnswer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentAnswer $studentAnswer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudentAnswer  $studentAnswer
     * @return \Illuminate\Http\Response
     */
    

    public function upload(Request $request)
    {
        $request->validate([
            'file_assessment' => 'required|mimes:csv,txt',
        ],
        [
            'file_assessment.mimes' => 'The file must be a file of type: csv, txt.',
            'file_assessment.required' => 'Please upload a file.',
        ]);

        $assessment = Assessment::find($request->assessment_id);
        $assessment_keys = CustomFunction::getAssessmentKeys($request->assessment_id);
        $data = array();
        $error = array();

        $file_assessment = $request->file('file_assessment');
        $csv_file_path   = $file_assessment->getRealPath();
        $assessment_csv  = fopen( $csv_file_path, 'r' );
        while(! feof($assessment_csv)) {
            $sheets[] = fgetcsv($assessment_csv,0,';');
        }
        
        foreach ($sheets as $students) {
            if($students) {
                $lrn = "";
                $score = 0;
                $answer = [];
                foreach ($students as $key => $value) {
                    if($key < 12){
                        $lrn .= $value;
                    }else{
                        $item_number = $key-11;
                        if(array_key_exists($item_number, $assessment_keys)){
                            $is_correct = ($assessment_keys[$item_number] == $value) ? true : false;
                            $score = ($assessment_keys[$item_number] == $value) ? $score+1 : $score;
                            $answer[$item_number] = array(
                                "answer" => $value,
                                "is_correct" => $is_correct
                            );
                        }
                    }
                }

                $student = Student::select('student_id')
                    ->join('tbl_student_classes', 'tbl_student_classes.student_id',  'tbl_students.id')
                    ->where('lrn', $lrn)
                    ->where('class_id', $request->class_id)
                    ->get();

                if($student->count() == 0){
                    $error[] = $lrn." does not exist on this class.";
                }else{
                    $data[$student[0]->student_id] = array(
                        "score" => $score,
                        "answer" => $answer
                    );
                }
            }
        }

        if (sizeof($error) == 0) {
            
            DB::beginTransaction();
            try {

                $is_class_assessment_existing = ClassAssessment::where('class_id', $request->class_id)
                    ->where('assessment_id', $request->assessment_id)
                    ->get();

                if($is_class_assessment_existing->count() == 0){
                    $class_assessment = new ClassAssessment;
                    $class_assessment->class_id = $request->class_id;
                    $class_assessment->assessment_id = $request->assessment_id;
                    $class_assessment->save();
                }else{
                    $class_assessment = $is_class_assessment_existing[0];
                }
                
               

                foreach ($data as $student_id => $answers) {
                    
                    $is_student_existing = StudentScore::where([
                        "class_assessment_id" => $request->assessment_id,
                        "student_id" => $student_id
                    ])->get();

                    if($is_student_existing->count() == 0){

                        $student_score = new StudentScore;
                        $student_score->student_id = $student_id;
                        $student_score->score = $answers['score'];
                        $student_score->class_assessment_id = $class_assessment->id;
                        $student_score->save();

                        foreach ($answers['answer'] as $item_number => $answer) {
                           
                            $student_answer = new StudentAnswer;
                            $student_answer->student_id = $student_id;
                            $student_answer->answer = $answer['answer'];
                            $student_answer->is_correct = $answer['is_correct'];
                            $student_answer->item_number = $item_number;
                            $student_answer->class_assessment_id = $class_assessment->id;
                            $student_answer->save();
                        }
                    }
                    
                }

                DB::commit();
                $result = true;

            } catch (Exception $e) {
                DB::rollBack();
                $result = $e->getMessage();
            }

            if($result === true) {
                return redirect('/periodicals/'.$request->assessment_id)->with('success', 'Class Assessment uploaded successfully.');
            } else {
                return back()->withErrors($result);
            }

        }else{
            return back()->withErrors($error);
        }

    }


    public function batch_update(Request $request)
    {
        DB::beginTransaction();
        try {
            
            $assessment_keys = CustomFunction::getAssessmentKeys($request->assessment_id);
            

            foreach ($request->student_answer_id as $key => $value) {
                $is_correct = ($request->answer[$key] == $assessment_keys[$request->item_number[$key]]) ? true : false;
                $student_answer = StudentAnswer::find($request->student_answer_id[$key]);
                $student_answer->answer = $request->answer[$key];
                $student_answer->is_correct = $is_correct;
                $student_answer->save();
            }

            $score = StudentAnswer::where('is_correct', 1)
                ->where('class_assessment_id', $request->class_assessment_id)
                ->where('student_id', $request->student_id)
                ->sum('is_correct');

            $student_score = StudentScore::where('class_assessment_id', $request->class_assessment_id)
                ->where('student_id', $request->student_id)
                ->first();
                
            $student_score->score = $score;
            $student_score->save();

            DB::commit();
            $result = true;

        } catch (Exception $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if($result === true) {
            return redirect('/class_assessments/'.$request->class_assessment_id)
                ->with('success', 'Student Answers successfully updated.');
        } else {
            return back()->withErrors($result);
        }
    }


}
