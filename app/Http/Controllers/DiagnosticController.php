<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentKey;
use App\Models\AssessmentOption;
use App\Models\AssessmentType;
use App\Models\ClassAssessment;
use App\Models\CustomFunction;
use App\Models\Teacher;
use App\Models\TeacherClass;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DiagnosticController extends Controller
{
    public function index()
    {

        $page = [
            'name' => 'Assessment',
            'sub_name' => 'Diagnostic',
            'title' => 'Diagnostic Test Management',
            'crumb' => ['Assessments' => '/assessments'],
        ];

        $diagnostic_type_id = AssessmentType::where('type', 'Diagnostic')->value('id');
        $teacher_id = Teacher::where('user_id', Auth::user()->id)->value('id');
        $assessments = Assessment::select(
            'tbl_assessments.title as assessment',
            'tbl_assessments.id as id',
            'level', 'tbl_subjects.title as subject',
            'period'
        )
            ->join('tbl_grade_levels', 'tbl_assessments.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_subjects', 'tbl_assessments.subject_id', 'tbl_subjects.id')
            ->join('tbl_periods', 'tbl_assessments.period_id', 'tbl_periods.id')
            ->where('teacher_id', $teacher_id)
            ->where('assessment_type_id', $diagnostic_type_id)
            ->where('academic_year_id', AcademicYear::active()->id)
            ->get();

        return view('diagnostics.index', compact(
            'page', 'assessments'
        ));
    }

    public function upload(Request $request)
    {

        $request->validate([
            'file_answer_key' => 'required|mimes:xlsx,xls',
        ], [
            'file_answer_key.required' => 'Please upload an ANSWER-KEY-UPLOADER.xlsx file. you can download the template from the Downloads section.',
            'file_answer_key.mimes' => 'The uploaded file must be an Excel file (xlsx or xls).',
        ]);

        $file_answer_key = $request->file('file_answer_key');
        $spreadsheet_answer_key = IOFactory::load($file_answer_key);
        $answer_keys = CustomFunction::verifyAnswerKeys($spreadsheet_answer_key);
        $diagnostic_type_id = AssessmentType::where('type', 'Diagnostic')->value('id');

        if (array_key_exists('title', $answer_keys)) {
            if ((int) $answer_keys['type'] !== (int) $diagnostic_type_id) {
                return redirect('/diagnostics')
                    ->withErrors(['Invalid Assessment Type. Please upload a Diagnostic assessment template.']);
            }

            DB::beginTransaction();
            try {

                Assessment::saveAnswerKeys($answer_keys);
                DB::commit();
                $result = true;

            } catch (Exception $e) {
                DB::rollBack();
                $result = $e->getMessage();
            }

            if ($result === true) {
                return redirect('/diagnostics')->with('success', 'Diagnostic Test Successfully uploaded');
            } else {
                return redirect('/diagnostics')->withErrors(['error' => $result]);
            }

        } else {
            return redirect('/diagnostics')->withErrors(['error' => $answer_keys]);
        }

    }

    public function show(Assessment $assessment)
    {

        $page = [
            'name' => 'Assessment',
            'title' => 'Diagnostic Test',
            'sub_name' => 'Diagnostic',
            'crumb' => [
                'Assessments' => '/diagnostics',
                'Diagnostic Test' => '/diagnostics',
                'View' => '/diagnostics/'.$assessment->id,

            ],
        ];

        $questions = AssessmentKey::where('assessment_id', $assessment->id)
            ->join('tbl_questions', 'tbl_assessment_keys.question_id', 'tbl_questions.id')
            ->get();
        $teacher = Teacher::where('user_id', Auth::user()->id)->first();

        $classes = TeacherClass::select(
            'tbl_teacher_classes.id as id', 'section', 'level'
        )->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->where('subject_id', $assessment->subject_id)
            ->where('grade_level_id', $assessment->grade_level_id)
            ->where('teacher_id', $teacher->id)
            ->get();

        $classRooms = CustomFunction::getClassrooms();
        $rooms = [];

        foreach ($classRooms as $classRoom) {
            $rooms = array_merge($rooms, $classRoom);
        }

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

        foreach ($questions as $question) {
            $options = AssessmentOption::select(
                'tbl_options.id', 'assignment', 'option', 'is_correct'
            )->join('tbl_options', 'tbl_assessment_options.option_id', 'tbl_options.id')
                ->where('question_id', $question->id)
                ->get();

            $answer_keys[] = [
                'question' => $question,
                'options' => $options,
            ];

        }

        $assessment = CustomFunction::getAssessmentDetails($assessment->id);

        return view('diagnostics.diagnostic_test', compact(
            'page', 'answer_keys', 'classes', 'assessment', 'rooms',
            'class_assessments'
        ));
    }
}
