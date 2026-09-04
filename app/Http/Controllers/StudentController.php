<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentKey;
use App\Models\ClassAssessment;
use App\Models\Classroom;
use App\Models\CustomFunction;
use App\Models\GradeLevel;
use App\Models\Person;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Strand;
use App\Models\Student;
use App\Models\StudentClassroom;
use App\Models\Teacher;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = [
            'name' => 'Student',
            'title' => 'Student Management',
            'crumb' => ['Student' => '/students'],
        ];

        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $students = Student::select(
            'tbl_students.id as id', 'lrn',
            'tbl_persons.first_name',
            'tbl_persons.middle_name',
            'tbl_persons.last_name',
            'tbl_persons.suffix',
            'tbl_persons.gender'
        )
            ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('school_id', $school_id)
            ->get();

        return view('students.index', compact(
            'page',
            'students',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $page = [
            'name' => 'Student',
            'title' => 'Student Management',
            'crumb' => ['Student' => '/students'],
        ];

        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $school = School::find($school_id);

        return view('students.create', compact(
            'page', 'school'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'birth_date' => 'required',
                'gender' => 'required',
            ]);

            $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
            $check_lrn = Student::where('lrn', $request->lrn)->first();

            if ($check_lrn) {
                return redirect()->to(url()->previous())->withErrors(['error' => 'Student already exists!']);
            } else {

                $person = new Person;
                $person->first_name = $request->first_name;
                $person->middle_name = $request->middle_name;
                $person->last_name = $request->last_name;
                $person->birth_date = $request->birth_date;
                $person->gender = $request->gender;
                $person->save();

                $user = new User;
                $user->person_id = $person->id;
                $user->username = $request->lrn;
                $user->password = bcrypt('12345');
                $user->classification = 'Student';
                $user->status = 1;
                $user->save();

                $student = new Student;
                $student->lrn = $request->lrn;
                $student->user_id = $user->id;
                $student->school_id = $school_id;
                $student->email = $request->lrn;
                $student->save();

            }
            DB::commit();
            $result = true;

        } catch (Exception $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if ($result === true) {
            return redirect('/students')->with('success', 'Student has been added successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => $result]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Student $student)
    {
        $page = [
            'name' => 'Student',
            'title' => 'Student Management',
            'crumb' => ['Student' => '/students', 'Edit' => '/students'.$student->id.'/edit'],
        ];

        $user = User::find($student->user_id);
        $person = Person::find($user->person_id);
        $details = CustomFunction::getUserDetails(Auth::user()->id);
        // $division = Division::where('name', $details['division'] )->first();
        $schools = School::all();

        return view('students.edit', compact(
            'page', 'schools', 'user', 'person', 'student'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Student  $student
     * @return Response
     */
    public function update(Request $request)
    {

        DB::beginTransaction();
        try {

            $student = Student::find($request->student_id);
            $student->lrn = $request->lrn;
            $student->school_id = $request->school_id;
            $student->save();

            $user = User::find($student->user_id);
            $user->username = $request->lrn;
            $user->save();

            $person = Person::find($user->person_id);
            $person->first_name = $request->first_name;
            $person->middle_name = $request->middle_name;
            $person->last_name = $request->last_name;
            $person->suffix = $request->suffix;
            $person->birth_date = $request->birth_date;
            $person->gender = $request->gender;
            $person->save();

            DB::commit();
            $result = true;

        } catch (Exception $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if ($result === true) {
            return redirect('/students/'.$request->student_id.'/edit')->with('success', 'Student has been updated successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => $result]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Student $student)
    {
        //
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

        // FUNCTION FOR UPLOADING SF1
        $classroom = Classroom::find($request->classroom_id);
        $spreadsheet = IOFactory::load($request->file('file'));

        $cell = $this->getCellPosition($spreadsheet);
        $class = $this->getClassDetails($spreadsheet);
        $error = $this->verifyClassDetails($class, $request->classroom_id);
        $teacher = Teacher::where('user_id', Auth::user()->id)->first();

        if (count($error) > 0) {
            return redirect()->to(url()->previous())->withErrors(['error' => $error]);
        }

        for ($x = $cell['start']; $x <= 100; $x++) {
            $lrn = $spreadsheet->getSheetByName("ENCODE here")->getCell($cell['lrn'].$x)->getValue();
            if (strlen($lrn) == 12) {
                $name = explode(',', $spreadsheet->getSheetByName("ENCODE here")->getCell($cell['name'].$x)->getValue());
                $gender = $spreadsheet->getSheetByName("ENCODE here")->getCell($cell['gender'].$x)->getValue();
                $birth_date = $spreadsheet->getSheetByName("ENCODE here")->getCell($cell['birth_date'].$x)->getValue();

                if ($class['is_shs'] == 1) {
                    $birth_date = Carbon::createFromFormat('m/d/Y', $birth_date)->format('Y-m-d');
                } else {
                    $birth_date = Carbon::createFromFormat('m-d-Y', $birth_date)->format('Y-m-d');
                }

                $class['students'][$lrn] = [
                    'name' => $name,
                    'gender' => $gender,
                    'birth_date' => $birth_date,
                ];
            }
        }

        DB::beginTransaction();
        try {

            foreach ($class['students'] as $lrn => $student) {

                $check_lrn = Student::where('lrn', $lrn)->first();

                if ($check_lrn) {
                    $student = $check_lrn;
                } else {

                    $person = new Person;
                    $person->first_name = $student['name'][1];
                    $person->middle_name = (isset($student['name'][2])) ? $student['name'][2] : '-';
                    $person->last_name = $student['name'][0];
                    $person->birth_date = $student['birth_date'];
                    $person->gender = $student['gender'];
                    $person->save();

                    $user = new User;
                    $user->person_id = $person->id;
                    $user->username = $lrn;
                    $user->password = bcrypt('12345');
                    $user->classification = 'Student';
                    $user->status = 1;
                    $user->save();

                    $student = new Student;
                    $student->lrn = $lrn;
                    $student->user_id = $user->id;
                    $student->school_id = $teacher->school_id;
                    $student->email = $lrn;
                    $student->save();
                }

                $check_classroom = StudentClassroom::where('classroom_id', $classroom->id)
                    ->where('student_id', $student->id)->get();

                if ($check_classroom->count() == 0) {
                    $student_classroom = new StudentClassroom;
                    $student_classroom->student_id = $student->id;
                    $student_classroom->classroom_id = $classroom->id;
                    $student_classroom->status = 1;
                    $student_classroom->is_uploaded = 1;
                    $student_classroom->save();
                }

            }

            DB::commit();
            $result = true;

        } catch (Exception $e) {
            DB::rollBack();
            $result = $e->getMessage();
        }

        if ($result === true) {
            return redirect('/classrooms/'.$classroom->id)->with('success', 'SF1 has been uploaded successfully.');
        } else {
            return redirect()->to(url()->previous())->withErrors(['error' => $result]);
        }
    }

    public function getCellPosition($spreadsheet)
    {

        $verifier = $spreadsheet->getActiveSheet()->getCell('AL10')->getValue();

        if ($verifier == 'Track and Strand') {

            $is_shs = 1;
            $cell = [
                'school_id' => 'S5',
                'academic_year' => 'S9',
                'grade_level' => 'AD9',
                'section' => 'I16',
                'total' => 'AC46',
                'strand' => 'AQ11',
                'semester' => 'I9',
                'lrn' => 'A',
                'name' => 'C',
                'gender' => 'K',
                'birth_date' => 'L',
                'start' => 20,
                'is_shs' => 1,
            ];

        } else {

            // School Year
            $sf1_2019 = trim($spreadsheet->getActiveSheet()->getCell('P4')->getValue());
            $sf1_2021 = trim($spreadsheet->getActiveSheet()->getCell('Q4')->getValue());
            $sf1_2022 = trim($spreadsheet->getActiveSheet()->getCell('Q4')->getValue());

            if ($sf1_2019 == 'School Year') {
                $version = '2019';
            } elseif ($sf1_2021 == 'School Year') {
                $version = '2021';
            } elseif ($sf1_2022 == 'School Year') {
                $version = '2022';
            } else {
                return redirect()->to(url()->previous())->withErrors(['error' => 'Unknown SF1 version']);
            }

            switch ($version) {
                case '2019':
                    $cell = [
                        'school_id' => 'F3',
                        'academic_year' => 'S4',
                        'grade_level' => 'AB4',
                        'section' => 'AH4',
                        'total' => 'X64',
                        'strand' => '',
                        'lrn' => 'A',
                        'name' => 'C',
                        'gender' => 'G',
                        'birth_date' => 'H',
                        'start' => 7,
                        'is_shs' => 0,
                    ];
                    break;
                case '2021':
                    $cell = [
                        'school_id' => 'F3',
                        'academic_year' => 'T4',
                        'grade_level' => 'AE4',
                        'section' => 'AM4',
                        'total' => 'X64',
                        'strand' => '',
                        'lrn' => 'A',
                        'name' => 'C',
                        'gender' => 'G',
                        'birth_date' => 'H',
                        'start' => 7,
                        'is_shs' => 0,
                    ];
                    break;
                case '2022':
                    $cell = [
                        'school_id' => 'F3',
                        'academic_year' => 'T4',
                        'grade_level' => 'AE4',
                        'section' => 'AM4',
                        'total' => 'X64',
                        'strand' => '',
                        'lrn' => 'A',
                        'name' => 'C',
                        'gender' => 'G',
                        'birth_date' => 'H',
                        'start' => 7,
                        'is_shs' => 0,
                    ];
                    break;
                default:
                    // code...
                    break;
            }

        }

        return $cell;
    }

    public function getClassDetails($spreadsheet)
    {

        $sheet = $spreadsheet->getActiveSheet()->toArray();
        $cell = $this->getCellPosition($spreadsheet);

        $class = [
            'school_id' => $spreadsheet->getActiveSheet()->getCell($cell['school_id'])->getValue(),
            'academic_year' => str_replace(' ', '', $spreadsheet->getActiveSheet()->getCell($cell['academic_year'])->getValue()),
            'grade_level' => trim($spreadsheet->getActiveSheet()->getCell($cell['grade_level'])->getValue()),
            'total' => trim($spreadsheet->getActiveSheet()->getCell($cell['total'])->getValue()),
            'section' => trim($spreadsheet->getActiveSheet()->getCell($cell['section'])->getValue()),
            'is_shs' => $cell['is_shs'],
            'students' => [],
        ];

        if ($cell['is_shs'] == 1) {
            $class['strand'] = $spreadsheet->getActiveSheet()->getCell($cell['strand'])->getValue();
            // $class['course'] = $spreadsheet->getActiveSheet()->getCell($cell['course'])->getValue();
            $class['semester'] = $spreadsheet->getActiveSheet()->getCell($cell['semester'])->getValue();
        }

        return $class;

    }

    public function verifyClassDetails($class, $classroom_id)
    {

        $error = [];
        $classroom = Classroom::find($classroom_id);

        $teacher = Teacher::where('user_id', Auth::user()->id)->first();
        $school = School::find($teacher->school_id);
        $academic_year = AcademicYear::active();
        $grade_level = GradeLevel::where('level', $class['grade_level'])->get();
        $section = Section::where('section', strtoupper($class['section']))
            ->where('school_id', $school->id)
            ->get();

        if ($class['school_id'] != $school->code) {
            $error[] = 'Invalid School Code.';
        }
        if ($class['academic_year'] != $academic_year->from.'-'.$academic_year->to) {
            $error[] = 'Invalid Academic Year';
        }

        if ($grade_level->count() == 0) {
            $error[] = 'Invalid Grade Level.';
        } else {
            if ($grade_level[0]->id != $classroom->grade_level_id) {
                $error[] = "SF1 Grade Level doesn't match in this classroom.";
            }
        }
        if ($section->count() == 0) {
            $error[] = 'Invalid Section.';
        } else {
            if ($section[0]->id != $classroom->section_id) {
                $error[] = "SF1 Section doesn't match in this classroom.";
            }
        }

        if ($class['is_shs'] == 1) {

            $strand = Strand::where('name', $class['strand'])->get();
            if ($strand->count() == 0) {
                $error[] = 'Invalid Strand.';
            } else {
                if ($strand[0]->id != $classroom->strand_id) {
                    $error[] = "SF1 Strand doesn't match in this classroom.";
                }
            }

            $semester = Semester::where('semester', 'LIKE', '%'.$class['semester'].'%')->get();
            if ($semester->count() == 0) {
                $error[] = 'Invalid semester.';
            } else {
                if ($semester[0]->id != $classroom->semester_id) {
                    $error[] = "SF1 semester doesn't match in this classroom.";
                }
            }
        }

        return $error;

    }

    // STUDENTS CLASS ASSESSMENT

    public function studentsClassAssessment()
    {

        $page = [
            'name' => 'Class Assessment',
            'title' => 'Class Assessment',
            'crumb' => ['Class Assessment' => '/students/class_assessments'],
        ];

        $student = Student::where('user_id', Auth::user()->id)->first();
        $academic_year = AcademicYear::active();
        $assessments = ClassAssessment::select(
            'tbl_class_assessments.id as id',
            'tbl_assessments.title as assessment', 'date', 'number_of_items',
            'tbl_subjects.title as subject', 'type', 'score'
        )
            ->join('tbl_assessments', 'tbl_class_assessments.assessment_id', 'tbl_assessments.id')
            ->join('tbl_teacher_classes', 'tbl_class_assessments.class_id', 'tbl_teacher_classes.id')
            ->join('tbl_assessment_types', 'tbl_assessments.assessment_type_id', 'tbl_assessment_types.id')
            ->join('tbl_subjects', 'tbl_assessments.subject_id', 'tbl_subjects.id')
            ->join('tbl_student_classes', 'tbl_class_assessments.class_id', 'tbl_student_classes.class_id')
            ->join('tbl_student_scores',
                'tbl_class_assessments.id',
                'tbl_student_scores.class_assessment_id'
            )->where([
                'tbl_student_classes.student_id' => $student->id,
                'tbl_student_scores.student_id' => $student->id,
                'academic_year_id' => $academic_year->id,
            ])->get();

        // dd($assessments);

        return view('students.class_assessments.index', compact(
            'page',
            'assessments',
        ));
    }

    public function studentsClassAssessmentShow(ClassAssessment $class_assessment)
    {

        $page = [
            'name' => 'Class Assessment',
            'title' => 'Class Assessment',
            'crumb' => [
                'Class Assessment' => '/students/class_assessments',
                'Result' => '',
            ],
        ];

        $student = Student::where('user_id', Auth::user()->id)->first();
        $assessment = Assessment::find($class_assessment->assessment_id);
        $get_result = ClassAssessment::getResults($class_assessment->id);
        $result = $get_result[$student->id];

        $questions = AssessmentKey::where('assessment_id', $assessment->id)
            ->select('code', 'item_number', 'tbl_questions.id as id', 'description')
            ->join('tbl_questions', 'tbl_assessment_keys.question_id', 'tbl_questions.id')
            ->join('tbl_competencies', 'tbl_questions.competency_id', 'tbl_competencies.id')
            ->get();
        $competencies = [];

        foreach ($questions as $key => $question) {

            $is_correct = $result['answers'][$question->item_number]['is_correct'];
            if (isset($competencies[$question->code]['total_corrects'])) {
                $competencies[$question->code]['total_corrects'] += $is_correct;
            } else {
                $competencies[$question->code]['total_corrects'] = $is_correct;
            }
            $competencies[$question->code]['description'] = $question->description;
            $competencies[$question->code]['items'][$question->item_number] = $is_correct;
        }

        return view('students.class_assessments.show', compact(
            'page',
            'result', 'assessment', 'competencies'
        ));
    }
}
