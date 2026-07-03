<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentKey;
use App\Models\AssessmentOption;
use App\Models\TeacherClass;

class AssessmentController extends Controller
{
    public function show(Assessment $assessment)
    {

        $page = [
            'name' => 'Assessment',
            'title' => 'Periodical Exam',
            'sub_name' => 'Periodical',
            'crumb' => [
                'Assessments' => '/periodicals',
                'Periodical Exam' => '/periodicals',
                'View' => '/periodicals/'.$assessment->id,

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

        return view('summatives.show', compact(
            'page', 'answer_keys', 'classes', 'assessment',
            'class_assessments'
        ));
    }
}
