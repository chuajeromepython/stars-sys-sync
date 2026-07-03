<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentKey;
use App\Models\ClassAssessment;
use App\Models\District;
use App\Models\Period;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentAnswer;
use App\Models\Teacher;
use App\Models\Track;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $page = [
            'name' => 'Report',
            'title' => 'Report Management',
            'crumb' => ['Reports' => '/reports'],
        ];

        $tracks = Track::all();
        $semesters = Semester::all();
        $periods = Period::all();

        $divisions = [
            'Division Supervisor',
            'Division Administrator',
            'Division Superintendent',
            'Assistant Division Superintendent',
            'Chief of CID',
            'Chief of SGOD',
        ];
        if (Auth::user()->classification == 'System Administrator') {
            $level = '5';
        }

        if (in_array(Auth::user()->classification, $divisions)) {
            $level = '4';
        }
        if (Auth::user()->classification == 'District Supervisor') {
            $level = '3';
        }
        if (Auth::user()->classification == 'School Head' || Auth::user()->classification == 'Department Head') {
            $level = '2';
        }
        if (Auth::user()->classification == 'Teacher') {
            $level = '1';
        }

        return view('reports.index', compact('page',
            'tracks', 'semesters', 'periods', 'level'
        ));

    }

    public function generate(Request $request)
    {

        $result = [];
        switch ($request->report_type_id) {
            case 'LC':
                $result = $this->generateLevelofCompetencies($request);
                break;
            case 'AL':
                $result = $this->generateAchievementLevel($request);
                break;
            case 'SA':
                $result = $this->generateScoreAnalysis($request);
                break;
            default:
                break;
        }

        return $result;
    }

    /*=============================================
        START OF CRITICAL FUNCTIONS HERE - CHARIZ XD
    ===============================================*/

    public function getAssessments($request)
    {

        $academic_year = AcademicYear::active();
        $assessments = Assessment::select(
            'tbl_assessments.id as id', 'school_id', 'district_id',
            'section_id', 'tbl_assessments.teacher_id', 'number_of_items', 'tbl_class_assessments.id as class_assessment_id'
        )
            ->join('tbl_class_assessments', 'tbl_assessments.id', 'tbl_class_assessments.assessment_id')
            ->join('tbl_teacher_classes', 'tbl_class_assessments.class_id', 'tbl_teacher_classes.id')
            ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
            ->join('tbl_schools', 'tbl_classrooms.school_id', 'tbl_schools.id')
            ->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
            ->join('tbl_divisions', 'tbl_districts.division_id', 'tbl_divisions.id')
            ->where('tbl_classrooms.academic_year_id', $academic_year->id)
            ->where('assessment_type_id', $request->assessment_type_id)
            ->where('tbl_assessments.grade_level_id', $request->grade_level_id)
            ->where('tbl_assessments.subject_id', $request->subject_id)
            ->where('tbl_assessments.period_id', $request->period_id);

        if ($request->track_id != null) {
            $assessments = $assessments->where('tbl_classrooms.track_id', $request->track_id);
        }
        if ($request->strand_id != null) {
            $assessments = $assessments->where('tbl_classrooms.strand_id', $request->strand_id);
        }
        if ($request->semester_id != null) {
            $assessments = $assessments->where('tbl_classrooms.semester_id', $request->semester_id);
        }

        $district_id = $request->district_id;
        $school_id = $request->school_id;
        $teacher_id = $request->teacher_id;

        if (Auth::user() == 'District Supervisor') {
            $district_id = DistrictSupervisor::where('user_id', Auth::user()->id)->value('district_id');
        }
        if (Auth::user() == 'Department Head') {
            $school_id = DepartmentHead::where('user_id', Auth::user()->id)->value('school_id');
            $district_id = School::where('id', $school_id)->value('district_id');
        }
        if (Auth::user() == 'School Head') {
            $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
            $district_id = School::where('id', $school_id)->value('district_id');
        }
        if (Auth::user() == 'Teacher') {
            $teacher_id = Teacher::where('user_id', Auth::user()->id)->value('id');
            $school_id = Teacher::where('user_id', Auth::user()->id)->value('school_id');
            $district_id = School::where('id', $school_id)->value('district_id');
        }

        if ($district_id != null || $district_id != 0) {
            $assessments = $assessments->where('district_id', $request->district_id);
        }
        if ($school_id != null) {
            $assessments = $assessments->where('school_id', $school_id);
        }
        if ($teacher_id != null) {
            $assessments = $assessments->where('tbl_assessments.teacher_id', $teacher_id);
        }

        $assessments = $assessments->get();

        return $assessments;

    }

    // Used in append of levels Header in achievement level
    public function getLevel($request)
    {

        if (Auth::user()->classification == 'District Supervisor') {
            if ($request->school_id != null) {
                if ($request->teacher_id != null) {
                    $level = 'Section';
                } else {
                    $level = 'Teacher';
                }
            } else {
                $level = 'School';
            }
        } elseif (Auth::user()->classification == 'School Head' ||
            Auth::user()->classification == 'Department Head') {
            if ($request->teacher_id != null) {
                $level = 'Section';
            } else {
                $level = 'Teacher';
            }
        } elseif (Auth::user()->classification == 'Teacher') {
            $level = 'Section';
        } else {
            if ($request->district_id != null) {
                if ($request->school_id != null) {
                    if ($request->teacher_id != null) {
                        $level = 'Section';
                    } else {
                        $level = 'Teacher';
                    }
                } else {
                    $level = 'School';
                }
            } else {
                $level = 'District';
            }
        }

        return $level;
    }

    public function generateLevelofCompetencies($request)
    {

        $assessments = $this->getAssessments($request);
        $data = [];

        foreach ($assessments as $assessment) {

            $questions = AssessmentKey::select(
                'competency_id', 'item_number', 'assessment_id', 'code', 'description'
            )->join('tbl_questions', 'tbl_assessment_keys.question_id', 'tbl_questions.id')
                ->join('tbl_competencies', 'tbl_questions.competency_id', 'tbl_competencies.id')
                ->where('assessment_id', $assessment->id)
                ->get();

            foreach ($questions as $key => $question) {
                $student_answers = StudentAnswer::where('item_number', $question->item_number)
                    ->where('class_assessment_id', $assessment->class_assessment_id);

                $test_takers = $student_answers->count();
                $correct_test_takers = $student_answers->where('is_correct', 1)->count();

                if (isset($data[$question->code])) {
                    $data[$question->code]['test_takers'] += $test_takers;
                    $data[$question->code]['correct_test_takers'] += $correct_test_takers;
                } else {
                    $data[$question->code] = [
                        'description' => $question->description,
                        'test_takers' => $test_takers,
                        'correct_test_takers' => $correct_test_takers,
                    ];
                }
            }

        }

        foreach ($data as $competency_code => $row) {
            $percentage = number_format((float) ($row['correct_test_takers'] / $row['test_takers'])
             * (100), 2, '.', '');
            if ($percentage <= 4) {
                $mastery = 'Absolutely No Mastery';
            } elseif ($percentage >= 5 && $percentage <= 14) {
                $mastery = 'Very Low';
            } elseif ($percentage >= 15 && $percentage <= 34) {
                $mastery = 'Low';
            } elseif ($percentage >= 35 && $percentage <= 65) {
                $mastery = 'Average';
            } elseif ($percentage >= 66 && $percentage <= 85) {
                $mastery = 'Moving Towards Mastery';
            } elseif ($percentage >= 86 && $percentage <= 95) {
                $mastery = 'Closely Approximating Mastery';
            } elseif ($percentage >= 96 && $percentage <= 100) {
                $mastery = 'Mastered';
            } else {
                $mastery = 'Undefined';
            }
            $data[$competency_code]['percentage'] = $percentage;
            $data[$competency_code]['mastery'] = $mastery;
        }

        return $data;

    }

    public function generateAchievementLevel($request)
    {

        $assessments = $this->getAssessments($request);
        $level = $this->getLevel($request);
        $data = [];

        foreach ($assessments as $key => $assessment) {

            if ($level == 'District') {
                $name = District::where('id', $assessment->district_id)->value('name');
            }
            if ($level == 'School') {
                $name = School::where('id', $assessment->school_id)->value('name');
            }
            if ($level == 'Teacher') {
                $name = User::select(
                    'tbl_teachers.id', 'username', 'user_id',
                    'first_name', 'middle_name', 'last_name', 'suffix'
                )->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
                    ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
                    ->where('tbl_teachers.id', $assessment->teacher_id)
                    ->first();
                $name = $name->last_name.', '.$name->first_name.' '.$name->middle_name;
            }
            if ($level == 'Section') {
                $name = Section::where('id', $assessment->section_id)->value('section');
            }

            $results = ClassAssessment::getResults($assessment->class_assessment_id);

            foreach ($results as $key => $student) {

                if (isset($data[$name][$student['achievement']]['count'])) {
                    $data[$name][$student['achievement']]['count'] += 1;
                } else {
                    $data[$name][$student['achievement']]['count'] = 1;
                }

            }

            if (isset($data[$name]['total'])) {
                $data[$name]['total'] += count($results);
            } else {
                $data[$name]['total'] = count($results);
            }

        }

        foreach ($data as $name => $achievements) {
            foreach ($achievements as $key => $value) {
                if ($key != 'total') {
                    $percentage = number_format((float) ($value['count'] / $achievements['total'])
                        * (100), 2, '.', '');
                    $data[$name][$key]['percentage'] = $percentage;
                }
            }
        }

        return [
            'header' => $level,
            'data' => $data,
        ];
    }

    public function generateScoreAnalysis($request)
    {

        $assessments = $this->getAssessments($request);
        $level = $this->getLevel($request);
        $data = [];
        $x = [];

        foreach ($assessments as $key => $assessment) {

            if ($level == 'District') {
                $name = District::where('id', $assessment->district_id)->value('name');
            }
            if ($level == 'School') {
                $name = School::where('id', $assessment->school_id)->value('name');
            }
            if ($level == 'Teacher') {
                $name = User::select(
                    'tbl_teachers.id', 'username', 'user_id',
                    'first_name', 'middle_name', 'last_name', 'suffix'
                )->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
                    ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
                    ->where('tbl_teachers.id', $assessment->teacher_id)
                    ->first();
                $name = $name->last_name.', '.$name->first_name.' '.$name->middle_name;
            }
            if ($level == 'Section') {
                $name = Section::where('id', $assessment->section_id)->value('section');
            }

            $results = ClassAssessment::getScoreAnalysis($assessment->class_assessment_id);

            if (! isset($assessment_results[$name]['results'][$assessment->class_assessment_id])) {

                if (isset($assessment_results[$name]['total_mps'])) {
                    $assessment_results[$name]['total_mps'] += $results['mps'];
                } else {
                    $assessment_results[$name]['total_mps'] = $results['mps'];
                }
                if (isset($assessment_results[$name]['total_mean'])) {
                    $assessment_results[$name]['total_mean'] += $results['mean'];
                } else {
                    $assessment_results[$name]['total_mean'] = $results['mean'];
                }
                if (isset($assessment_results[$name]['total_sd'])) {
                    $assessment_results[$name]['total_sd'] += $results['sd'];
                } else {
                    $assessment_results[$name]['total_sd'] = $results['sd'];
                }
                if (isset($assessment_results[$name]['total_hpg'])) {
                    $assessment_results[$name]['total_hpg'] += $results['hpg'];
                } else {
                    $assessment_results[$name]['total_hpg'] = $results['hpg'];
                }
                if (isset($assessment_results[$name]['total_apg'])) {
                    $assessment_results[$name]['total_apg'] += $results['apg'];
                } else {
                    $assessment_results[$name]['total_apg'] = $results['apg'];
                }
                if (isset($assessment_results[$name]['total_lpg'])) {
                    $assessment_results[$name]['total_lpg'] += $results['lpg'];
                } else {
                    $assessment_results[$name]['total_lpg'] = $results['lpg'];
                }

                if (isset($assessment_results[$name]['total_proficiency'])) {
                    $assessment_results[$name]['total_proficiency'] += $results['proficiency'];
                } else {
                    $assessment_results[$name]['total_proficiency'] = $results['proficiency'];
                }

                $assessment_results[$name]['results'][$assessment->class_assessment_id] = [
                    'id' => $results['id'],
                    'mean' => $results['mean'],
                    'mps' => $results['mps'],
                    'sd' => $results['sd'],
                    'hpg' => $results['hpg'],
                    'apg' => $results['apg'],
                    'lpg' => $results['lpg'],
                    'proficiency' => $results['proficiency'],
                ];
            }
        }

        if (! empty($assessment_results)) {
            foreach ($assessment_results as $name => $assessment_result) {
                $x = count($assessment_result['results']);
                $data[$name]['mean'] = number_format((float) ($assessment_result['total_mean'] / $x), 2, '.', '');
                $data[$name]['mps'] = number_format((float) ($assessment_result['total_mps'] / $x), 2, '.', '');
                $data[$name]['sd'] = number_format((float) ($assessment_result['total_sd'] / $x), 2, '.', '');
                $data[$name]['proficiency'] = number_format((float) ($assessment_result['total_proficiency'] / $x), 2, '.', '');
                $data[$name]['hpg'] = $assessment_result['total_hpg'];
                $data[$name]['apg'] = $assessment_result['total_apg'];
                $data[$name]['lpg'] = $assessment_result['total_lpg'];
            }
        }

        return [
            'header' => $level,
            'size' => count($data),
            'data' => $data,
        ];
    }
}
