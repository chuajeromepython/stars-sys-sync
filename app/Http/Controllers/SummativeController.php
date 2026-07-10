<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentKey;
use App\Models\AssessmentOption;
use App\Models\ClassAssessment;
use App\Models\CustomFunction;
use App\Models\Period;
use App\Models\Summative;
use App\Models\Teacher;
use App\Models\TeacherClass;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SummativeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Assessment',
            'sub_name' => 'Summative',
            'title' => 'Summative Test',
            'crumb' => ['Assessments' => '/assessments'],
        ];
        $periods = Period::all();
        $teacher_id = Teacher::where('user_id', Auth::user()->id)->value('id');
        $assessments = Assessment::select(
            'tbl_assessments.title as assessment',
            'tbl_assessments.id as id',
            'level', 'tbl_subjects.title as subject',
            'period', 'summative_number'
        )->join('tbl_grade_levels', 'tbl_assessments.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_subjects', 'tbl_assessments.subject_id', 'tbl_subjects.id')
            ->join('tbl_periods', 'tbl_assessments.period_id', 'tbl_periods.id')
            ->join('tbl_summatives', 'tbl_assessments.id', 'tbl_summatives.assessment_id')
            ->where('teacher_id', $teacher_id)
            ->where('assessment_type_id', 2)
            ->where('academic_year_id', AcademicYear::active()->id)
            ->get();

        return view('summatives.index', compact(
            'page', 'assessments', 'periods'
        ));
    }

    public function show(Assessment $assessment)
    {

        $page = [
            'name' => 'Assessment',
            'title' => 'Summative Test',
            'sub_name' => 'Summative',
            'crumb' => [
                'Assessments' => '/summatives',
                'Summative Test' => '/summatives',
                'View' => '/summatives/'.$assessment->id,

            ],
        ];

        $questions = AssessmentKey::where('assessment_id', $assessment->id)
            ->join('tbl_questions', 'tbl_assessment_keys.question_id', 'tbl_questions.id')
            ->get();

        $classes = TeacherClass::select(
            'tbl_teacher_classes.id as id', 'section', 'level'
        )->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->where('subject_id', $assessment->subject_id)
            ->where('grade_level_id', $assessment->grade_level_id)
            ->get();

        $class_assessments = ClassAssessment::select(
            'tbl_class_assessments.id', 'section', 'level', 'period'
        )->join('tbl_teacher_classes', 'tbl_class_assessments.class_id', 'tbl_teacher_classes.id')
            ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->join('tbl_assessments', 'tbl_class_assessments.assessment_id', 'tbl_assessments.id')
            ->join('tbl_periods', 'tbl_assessments.period_id', 'tbl_periods.id')
            ->where('assessment_id', $assessment->id)
            ->get();

        $answer_keys = [];

        foreach ($questions as $key => $question) {
            $options = AssessmentOption::select(
                'assignment', 'option', 'is_correct'
            )->join('tbl_options', 'tbl_assessment_options.option_id', 'tbl_options.id')
                ->where('question_id', $question->id)
                ->get();

            $answer_keys[] = [
                'question' => $question,
                'options' => $options,
            ];
        }
        $summative = Summative::where('assessment_id', $assessment->id)->first();
        $assessment = CustomFunction::getAssessmentDetails($assessment->id);

        $classRooms = CustomFunction::getClassrooms();
        $rooms = [];

        foreach ($classRooms as $classRoom) {
            $rooms = array_merge($rooms, $classRoom);
        }

        return view('summatives.show', compact(
            'page', 'answer_keys', 'classes', 'assessment',
            'class_assessments', 'summative', 'rooms'
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Summative  $summative
     * @return Response
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file_answer_key' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file_answer_key.required' => 'Please upload a file.',
            'file_answer_key.mimes' => 'The file must be an Excel file (xlsx or xls).',
            'file_answer_key.max' => 'The file size must not exceed 10MB.',
        ]);

        $file_answer_key = $request->file('file_answer_key');
        $spreadsheet_answer_key = IOFactory::load($file_answer_key);
        $answer_keys = CustomFunction::verifyAnswerKeys($spreadsheet_answer_key);

        if (array_key_exists('title', $answer_keys)) {
            DB::beginTransaction();
            try {

                $teacher_id = Teacher::where('user_id', Auth::user()->id)->value('id');
                $is_summative_existing = Assessment::select()
                    ->join('tbl_summatives', 'tbl_assessments.id', 'tbl_summatives.id')
                    ->where([
                        'period_id' => $answer_keys['period'],
                        'grade_level_id' => $answer_keys['grade'],
                        'subject_id' => $answer_keys['subject'],
                        'summative_number' => $request->summative_number,
                        'tbl_assessments.teacher_id' => $teacher_id,
                    ])->get();

                if ($is_summative_existing->count() == 0) {

                    $assessment = Assessment::saveAnswerKeys($answer_keys);

                    $summative = new Summative;
                    $summative->summative_number = $request->summative_number;
                    $summative->assessment_id = $assessment->id;
                    $summative->save();

                } else {
                    return back()->withErrors('Summative Test already uploaded in this class');
                }

                DB::commit();
                $result = true;

            } catch (Exception $e) {
                DB::rollBack();
                $result = $e->getMessage();
            }

            if ($result === true) {
                return redirect('/summatives')->with('success', 'Summative Test Successfully uploaded');
            } else {
                return redirect('/summatives')->withErrors($result);
            }

        } else {
            return redirect('/summatives')->withErrors($answer_keys);
        }

    }
}
