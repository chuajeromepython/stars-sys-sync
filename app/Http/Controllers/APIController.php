<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentKey;
use App\Models\AssessmentOption;
use App\Models\AssessmentType;
use App\Models\AssistantDivisionSuperIntendent;
use App\Models\ChiefCID;
use App\Models\ChiefSGOD;
use App\Models\Course;
use App\Models\District;
use App\Models\Division;
use App\Models\DivisionAdministrator;
use App\Models\DivisionSuperIntendent;
use App\Models\DivisionSupervisor;
use App\Models\ECDC;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\Section;
use App\Models\Strand;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClass;
use App\Models\User;
use App\Support\UserHierarchy;
use Auth;
use Illuminate\Http\Request;

class APIController extends Controller
{
    public function __construct(private UserHierarchy $userHierarchy) {}

    /************************************************
    This Controller is mainly use for Ajax get Data.
    **************************************************/

    public function getArea(Request $request)
    {

        $classification = $request->classification;
        $data = [];

        if ($classification == 'Teacher' ||
            $classification == 'School Head' ||
            $classification == 'Department Head' ||
            $classification == 'Student'
        ) {
            if (Auth::user()->classification == 'System Administrator') {
                $data = School::all();
            } elseif (Auth::user()->classification == 'Division Administrator') {
                $division = DivisionAdministrator::where('user_id', Auth::user()->id)->first();
                $data = School::select('tbl_schools.*')
                    ->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
                    ->where('tbl_districts.division_id', $division->division_id)
                    ->get();
            } elseif (Auth::user()->classification == 'School Head') {
                $school = SchoolSupervisor::where('user_id', Auth::user()->id)->first();
                $data = School::select('tbl_schools.*')
                    ->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
                    ->where('tbl_schools.id', $school->school_id)
                    ->get();
            }
        }

        if ($classification == 'Division Supervisor' ||
            $classification == 'District Supervisor' ||
            $classification == 'Division Administrator' ||
            $classification == 'Division Superintendent' ||
            $classification == 'Assistant Division Superintendent' ||
            $classification == 'Chief of CID' ||
            $classification == 'Chief of SGOD'
        ) {
            if (Auth::user()->classification == 'System Administrator') {
                $data = Division::all();
            } else {
                $division = DivisionAdministrator::where('user_id', Auth::user()->id)->first();
                $data = Division::where('id', $division->division_id)->get();
            }
        }

        if ($classification == 'District Supervisor') {
            if (Auth::user()->classification == 'System Administrator') {
                $data = District::all();
            } else {
                $division = DivisionAdministrator::where('user_id', Auth::user()->id)->first();
                $data = District::where('division_id', $division->division_id)->get();
            }
        }

        return $data;

    }

    public function getStrands(Request $request)
    {
        $strands = Strand::where('track_id', $request->track_id)->get();

        return $strands;
    }

    public function getCourses(Request $request)
    {
        $courses = Course::where('strand_id', $request->strand_id)->get();

        return $courses;
    }

    public function getTeachers(Request $request)
    {
        $hierarchy = $this->userHierarchy->forUser(Auth::user());

        $teachers = User::select(
            'tbl_teachers.id', 'username', 'user_id',
            'first_name', 'middle_name', 'last_name', 'suffix'
        )->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->join('tbl_schools', 'tbl_teachers.school_id', 'tbl_schools.id')
            ->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
            ->where('classification', 'Teacher');

        match ($hierarchy['scope']) {
            'teacher' => $teachers->where('tbl_teachers.id', $hierarchy['teacher_id']),
            'school' => $teachers->where('tbl_teachers.school_id', $hierarchy['school_id']),
            'district' => $teachers->where('tbl_schools.district_id', $hierarchy['district_id']),
            'division' => $teachers->where('tbl_districts.division_id', $hierarchy['division_id']),
            'none' => $teachers->whereRaw('1 = 0'),
            default => null,
        };

        if ($request->filled('school_id')) {
            $teachers->where('tbl_teachers.school_id', $request->integer('school_id'));
        }

        return $teachers
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function getUserDivision()
    {

        switch (Auth::user()->classification) {
            case 'Division Administrator':
                $division_id = DivisionAdministrator::where('user_id', Auth::user()->id)
                    ->value('division_id');
                break;
            case 'Division Supervisor':
                $division_id = DivisionSupervisor::where('user_id', Auth::user()->id)
                    ->value('division_id');
                break;
            case 'Division Superintendent':
                $division_id = DivisionSuperIntendent::where('user_id', Auth::user()->id)
                    ->value('division_id');
                break;
            case 'Assistant Division Superintendent':
                $division_id = AssistantDivisionSuperIntendent::where('user_id', Auth::user()->id)
                    ->value('division_id');
                break;
            case 'Chief of CID':
                $division_id = ChiefCID::where('user_id', Auth::user()->id)
                    ->value('division_id');
                break;
            case 'Chief of SGOD':
                $division_id = ChiefSGOD::where('user_id', Auth::user()->id)
                    ->value('division_id');
                break;
            default:
                $division_id = null;
                break;
        }

        return $division_id;
    }

    public function getDistricts()
    {
        $hierarchy = $this->userHierarchy->forUser(Auth::user());
        $districts = District::query();

        match ($hierarchy['scope']) {
            'division' => $districts->where('division_id', $hierarchy['division_id']),
            'district', 'school', 'teacher' => $districts->whereKey($hierarchy['district_id']),
            'none' => $districts->whereRaw('1 = 0'),
            default => null,
        };

        return $districts->orderBy('name')->get();
    }

    public function getDistrictsPerDivision(Request $request)
    {
        $districts = District::where('division_id', $request->division_id)->get();

        return $districts;
    }

    public function getSchools(Request $request)
    {
        $hierarchy = $this->userHierarchy->forUser(Auth::user());
        $schools = School::query()
            ->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
            ->select('tbl_schools.*');

        match ($hierarchy['scope']) {
            'teacher', 'school' => $schools->where('tbl_schools.id', $hierarchy['school_id']),
            'district' => $schools->where('tbl_schools.district_id', $hierarchy['district_id']),
            'division' => $schools->where('tbl_districts.division_id', $hierarchy['division_id']),
            'none' => $schools->whereRaw('1 = 0'),
            default => null,
        };

        if ($request->filled('district_id') && $request->integer('district_id') > 0) {
            $schools->where('tbl_schools.district_id', $request->integer('district_id'));
        }

        if ($request->filled('grade_level_id')) {
            $gradeLevelId = $request->integer('grade_level_id');
            $schoolTypeIds = match (true) {
                ($gradeLevelId >= 1 && $gradeLevelId <= 6) || $gradeLevelId === 13 => [1],
                $gradeLevelId >= 7 && $gradeLevelId <= 10 => [2, 4],
                $gradeLevelId >= 11 && $gradeLevelId <= 12 => [3, 4],
                default => [5],
            };

            $schools->whereIn('school_type_id', $schoolTypeIds);
        }

        return $schools->orderBy('name')->get();
    }

    public function getStudentAnswers(Request $request)
    {

        $answers = StudentAnswer::where([
            'student_id' => $request->student_id,
            'class_assessment_id' => $request->class_assessment_id,
        ])->get();

        return $answers;

    }

    public function searchLRN(Request $request)
    {

        $student = Student::select(
            'tbl_students.id as id', 'lrn',
            'tbl_persons.first_name',
            'tbl_persons.middle_name',
            'tbl_persons.last_name',
            'tbl_persons.suffix',
            'tbl_persons.gender'
        )
            ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('lrn', $request->lrn)
            ->get();
        $result = [];

        if ($student->count() > 0) {
            $result = [
                'message' => 'Student Successfully Found!',
                'student' => $student[0],
            ];
        } else {
            $result = [
                'error' => 'Student Not Found',
                'student' => null,
            ];
        }

        return $result;

    }

    public function getGradeLevelPerEducationLevel(Request $request)
    {

        if ($request->education_level_id == 1) {
            $ids = [1, 2, 3, 4, 5, 6, 13];
        }
        if ($request->education_level_id == 2) {
            $ids = [7, 8, 9, 10];
        }
        if ($request->education_level_id == 3) {
            $ids = [11, 12];
        }

        $grade_levels = GradeLevel::whereIn('id', $ids)->get();

        return $grade_levels;
    }

    public function getAssessmentTypePerGradeLevel(Request $request)
    {
        $assessment_types = AssessmentType::whereIn('type', ['Summative', 'Term Exam', 'Diagnostic'])->get();

        return $assessment_types;
    }

    public function getSubjectClassPerGradeLevel(Request $request)
    {

        if (Auth::user()->classification == 'Teacher') {
            $teacher_id = Teacher::where('user_id', Auth::user()->id)->value('id');
            $subject_ids = TeacherClass::select('subject_id')
                ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
                ->where('grade_level_id', $request->grade_level_id)
                ->where('teacher_id', $teacher_id)
                ->groupBy('subject_id')
                ->get();
            $subjects = Subject::whereIn('id', $subject_ids)->get();
        } else {
            $subjects = Subject::all();
        }

        return $subjects;
    }

    public function getGradeLevelPerAcademicYear(Request $request)
    {

        $teacher = Teacher::where('user_id', Auth::user()->id)->first();
        $get_assessment_grade_levels = Assessment::select('grade_level_id')
            ->where([
                'teacher_id' => $teacher->id,
                'academic_year_id' => $request->academic_year_id,
            ])->groupBy('grade_level_id')->get();

        $grade_levels = GradeLevel::whereIn('id', $get_assessment_grade_levels)->get();

        return $grade_levels;
    }

    public function getSubjectPerGradeLevel(Request $request)
    {

        $teacher = Teacher::where('user_id', Auth::user()->id)->first();
        $get_assessment_subject = Assessment::select('subject_id')
            ->where([
                'teacher_id' => $teacher->id,
                'academic_year_id' => $request->academic_year_id,
                'grade_level_id' => $request->grade_level_id,
            ])->groupBy('subject_id')->get();

        $subjects = Subject::whereIn('id', $get_assessment_subject)->get();

        return $subjects;
    }

    public function getItems(Request $request)
    {
        $teacher = Teacher::where('user_id', Auth::user()->id)->first();

        $assessments = Assessment::select('id')
            ->where([
                'teacher_id' => $teacher->id,
                'academic_year_id' => $request->academic_year_id,
                'grade_level_id' => $request->grade_level_id,
                'subject_id' => $request->subject_id,
            ])->get();

        $questions = AssessmentKey::select('code', 'question', 'question_id')
            ->join('tbl_questions', 'tbl_assessment_keys.question_id', 'tbl_questions.id')
            ->join('tbl_competencies', 'tbl_questions.competency_id', 'tbl_competencies.id')
            ->whereIn('assessment_id', $assessments)
            ->get();
        $items = [];

        foreach ($questions as $key => $question) {
            $options = AssessmentOption::select(
                'assignment', 'option', 'is_correct'
            )->join('tbl_options', 'tbl_assessment_options.option_id', 'tbl_options.id')
                ->where('question_id', $question->question_id)
                ->get();

            $items[] = [
                'code' => $question->code,
                'question' => $question->question,
                'options' => $options,
            ];
        }

        return $items;
    }

    public function getTeachersPerSchool(Request $request)
    {

        $school_id = $request->school_id;
        $teachers = Teacher::select(
            'tbl_teachers.id as id',
            'first_name', 'middle_name', 'last_name'
        )->join('tbl_users', 'tbl_teachers.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('school_id', $school_id)->get();

        return $teachers;

    }

    public function getSectionsPerSchool(Request $request)
    {

        $school_id = $request->school_id;
        $sections = Section::where('school_id', $school_id)->get();

        return $sections;

    }

    public function getECDCResult(Request $request)
    {

        $results = ECDC::getResults($request->ecdc_id);

        return $results;

    }
}
