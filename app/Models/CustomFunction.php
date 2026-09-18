<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomFunction extends Model
{
    use HasFactory;

    /************************************************
    This Model is mainly use for Custom functions
    to avoid code redundancy.
     **************************************************/

    // NERRIE'S NOTE
    // This function is used only for testing purposes
    // Do not activate this function in production mode
    public static function truncate()
    {
        // Assessments
        Assessment::query()->truncate();
        AssessmentKey::query()->truncate();
        StudentAnswer::query()->truncate();
        StudentScore::query()->truncate();
        Question::query()->truncate();
        Option::query()->truncate();
        AssessmentOption::query()->truncate();
        ClassAssessment::query()->truncate();

        // ECD
        ECDC::query()->truncate();
        StudentECDC::query()->truncate();

        // Classroom
        Classroom::query()->truncate();
        TeacherClass::query()->truncate();
        StudentClass::query()->truncate();
        Section::query()->truncate();

        // Users
        User::query()->truncate();
        Person::query()->truncate();
        DivisionSupervisor::query()->truncate();
        DivisionAdministrator::query()->truncate();
        DivisionSuperIntendent::query()->truncate();
        AssistantDivisionSuperIntendent::query()->truncate();
        ChiefCID::query()->truncate();
        ChiefSGOD::query()->truncate();
        DistrictSupervisor::query()->truncate();
        SchoolSupervisor::query()->truncate();
        Teacher::query()->truncate();
        DepartmentHead::query()->truncate();
        Student::query()->truncate();

        DB::table('tbl_users')->insert([
            'username' => 'glennnerrie',
            'status' => '1',
            'classification' => 'Division Administrator',
            'password' => Hash::make('password'),
            'person_id' => '1',
        ]);

        DB::table('tbl_users')->insert([
            'username' => 'sysad',
            'status' => '1',
            'classification' => 'System Administrator',
            'password' => Hash::make('password'),
            'person_id' => '1',
        ]);
        DB::table('tbl_persons')->insert([
            'first_name' => 'Nerrie',
            'middle_name' => 'Agbagala',
            'last_name' => 'Afurong',
            'gender' => 'F',
            'birth_date' => '1998-01-19',
        ]);
        DB::table('tbl_division_administrators')->insert([
            'status' => '1',
            'user_id' => '1',
            'division_id' => '1',
            'email' => 'glennnerrie@yahoo.com',
        ]);
    }

    // NERRIE'S NOTE:
    // This function is used in user > create > classification select field.
    // Auth User can create another user but limited only according to their role
    public static function filterCreateClassification()
    {

        $user_classification = Auth::user()->classification;
        switch ($user_classification) {
            case 'System Administrator':
                $filter = ['Division Administrator'];
                break;
            case 'Division Administrator':
                $filter = [
                    'Division Supervisor',
                    'District Supervisor',
                    'School Head',
                    'Division Superintendent',
                    'Assistant Division Superintendent',
                    'Chief of CID',
                    'Chief of SGOD',
                ];
                break;
            case 'School Head':
                $filter = [
                    'Department Head',
                    'Teacher',
                ];
                break;
        }

        return $filter;
    }

    // NERRIE'S NOTE:
    // This function is used globally
    // Auth User can create another user but limited only according to their role
    public static function filterViewClassification()
    {

        $user_classification = Auth::user()->classification;

        switch ($user_classification) {
            case 'System Administrator':
                $filter = [
                    'Division Supervisor',
                    'District Supervisor',
                    'School Head',
                    'Teacher',
                    'Student',
                    'Division Administrator',
                    'Division Superintendent',
                    'Assistant Division Superintendent',
                    'Chief of CID',
                    'Chief of SGOD',
                    'Department Head',
                ];
                break;
            case 'Division Administrator':
                $filter = [
                    'Division Supervisor',
                    'District Supervisor',
                    'School Head',
                    'Teacher',
                    'Division Administrator',
                    'Division Superintendent',
                    'Assistant Division Superintendent',
                    'Chief of CID',
                    'Chief of SGOD',
                    'Department Head',
                ];
                break;
            case 'School Head':
                $filter = [
                    'Department Head',
                    'Teacher',
                ];
                break;
        }

        return $filter;
    }

    // NERRIE'S NOTE:
    // This function is used globally
    // This will return user details by passing user_id in the parameter(s)
    public static function getUserDetails($user_id)
    {

        $user = User::find($user_id);
        $person = Person::find($user->person_id);
        $fullname = $person->first_name.' '.$person->middle_name;
        $fullname .= ' '.$person->last_name.' '.$person->suffix;
        $division = null;
        $district = null;
        $school = null;
        $subjects = null;
        $level = null;
        switch ($user->classification) {

            case 'Division Supervisor':
                $supervisor = DivisionSupervisor::where('user_id', $user->id)->first();
                $division = Division::find($supervisor->division_id);
                $subjects = Subject::select('title')
                    ->whereIn('id', json_decode($supervisor->subject_id))->get();
                $level = 'Division';
                break;

            case 'Division Administrator':
                $division_id = DivisionAdministrator::where('user_id', $user->id)
                    ->value('division_id');
                $division = Division::find($division_id);
                $level = 'Division';
                break;

            case 'Division Superintendent':
                $division_id = DivisionSuperIntendent::where('user_id', $user->id)
                    ->value('division_id');
                $division = Division::find($division_id);
                $level = 'Division';
                break;

            case 'Assistant Division Superintendent':
                $division_id = AssistantDivisionSuperIntendent::where('user_id', $user->id)
                    ->value('division_id');
                $division = Division::find($division_id);
                $level = 'Division';
                break;

            case 'Chief of CID':
                $division_id = ChiefCID::where('user_id', $user->id)
                    ->value('division_id');
                $division = Division::find($division_id);
                $level = 'Division';
                break;

            case 'Chief of SGOD':
                $division_id = ChiefSGOD::where('user_id', $user->id)
                    ->value('division_id');
                $division = Division::find($division_id);
                $level = 'Division';
                break;

            case 'District Supervisor':
                $district_id = DistrictSupervisor::where('user_id', $user->id)->value('district_id');
                $district = District::find($district_id);
                $division = Division::find($district->division_id);
                $level = 'District';
                break;

            case 'School Head':
                $school_id = SchoolSupervisor::where('user_id', $user->id)->value('school_id');
                $school = School::find($school_id);
                $district = District::find($school->district_id);
                $division = Division::find($district->division_id);
                $level = 'School';
                break;

            case 'Teacher':
                $school_id = Teacher::where('user_id', $user->id)->value('school_id');
                $school = School::find($school_id);
                $district = District::find($school->district_id);
                $division = Division::find($district->division_id);
                $level = 'School';
                break;

            case 'Student':
                $school_id = Student::where('user_id', $user->id)->value('school_id');
                $school = School::find($school_id);
                $district = District::find($school->district_id);
                $division = Division::find($district->division_id);
                $level = 'School';
                break;

            case 'Department Head':
                $head = DepartmentHead::where('user_id', $user->id)->first();
                $school = School::find($head->school_id);
                $district = District::find($school->district_id);
                $division = Division::find($district->division_id);
                $subjects = Subject::select('title')
                    ->whereIn('id', json_decode($head->subject_id))->get();
                $level = 'School';
                break;
            case 'System Administrator':
                break;
            default:
                return redirect('/forbidden');
                break;
        }

        $details = [
            'classification' => $user->classification,
            'fullname' => $fullname,
            'birth_date' => $person->birth_date,
            'gender' => ($person->gender == 'F') ? 'Female' : 'Male',
            'division' => ($division == null) ? $division : $division->name,
            'district' => ($district == null) ? $district : $district->name,
            'school' => ($school == null) ? $school : $school->name,
            'level' => $level,
            'subjects' => $subjects,
        ];

        return $details;
    }

    // NERRIE'S NOTE:
    // This function is used globally
    // This will return user classification history by passing user_id in the parameter(s)
    public static function getUserClassificationHistory($user_id)
    {

        $histories = ClassificationHistory::where('user_id', $user_id)->get();

        $details = [];

        foreach ($histories as $history) {

            $user = User::find($history->encoder_user_id);
            $person = Person::find($user->person_id);
            $encoder = $person->first_name.' '.$person->middle_name;
            $encoder .= ' '.$person->last_name.' '.$person->suffix;

            $division = null;
            $district = null;
            $school = null;
            $subjects = null;

            switch ($history->classification) {

                case 'Division Supervisor':
                    $supervisor = DivisionSupervisor::where('user_id', $user_id)->first();
                    $division = Division::find($supervisor->division_id);
                    break;

                case 'Division Administrator':
                    $division_id = DivisionAdministrator::where('user_id', $user_id)
                        ->value('division_id');
                    $division = Division::find($division_id);
                    break;

                case 'Division Superintendent':
                    $division_id = DivisionSuperIntendent::where('user_id', $user_id)
                        ->value('division_id');
                    $division = Division::find($division_id);
                    break;

                case 'Assistant Division Superintendent':
                    $division_id = AssistantDivisionSuperIntendent::where('user_id', $user_id)
                        ->value('division_id');
                    $division = Division::find($division_id);
                    break;

                case 'Chief of CID':
                    $division_id = ChiefCID::where('user_id', $user_id)
                        ->value('division_id');
                    $division = Division::find($division_id);
                    break;

                case 'Chief of SGOD':
                    $division_id = ChiefSGOD::where('user_id', $user_id)
                        ->value('division_id');
                    $division = Division::find($division_id);
                    break;

                case 'District Supervisor':
                    $district_id = DistrictSupervisor::where('user_id', $user_id)->value('district_id');
                    $district = District::find($district_id);
                    $division = Division::find($district->division_id);
                    break;

                case 'School Head':
                    $school_id = SchoolSupervisor::where('user_id', $user_id)->value('school_id');
                    $school = School::find($school_id);
                    $district = District::find($school->district_id);
                    $division = Division::find($district->division_id);
                    break;

                case 'Teacher':
                    $school_id = Teacher::where('user_id', $user_id)->value('school_id');
                    $school = School::find($school_id);
                    $district = District::find($school->district_id);
                    $division = Division::find($district->division_id);
                    break;

                case 'Department Head':
                    $head = DepartmentHead::where('user_id', $user_id)->first();
                    $school = School::find($head->school_id);
                    $district = District::find($school->district_id);
                    $division = Division::find($district->division_id);
                    break;

                default:
                    return redirect('/forbidden');
                    break;
            } // end of switch

            $details[] = [
                'classification' => $history->classification,
                'created_at' => $history->created_at,
                'encoder' => $encoder,
                'birth_date' => $person->birth_date,
                'gender' => ($person->gender == 'F') ? 'Female' : 'Male',
                'division' => ($division == null) ? $division : $division->name,
                'district' => ($district == null) ? $district : $district->name,
                'school' => ($school == null) ? $school : $school->name,
            ];
        }

        return $details;
    }

    // NERRIE'S NOTE:
    // This function is used in filter of Area.
    // Area will depend on Auth user classification

    public static function dynamicAreaOption()
    {
        switch (Auth::user()->classification) {
            case 'System Administrator':
                $options = [
                    'divisions' => Division::all(),
                    'subjects' => Subject::all(),
                    'districts' => District::all(),
                    'schools' => School::all(),
                ];
                break;
            case 'Division Administrator':
                $division_id = Auth::user()->divisionadministrator->division_id;
                $options = [
                    'divisions' => Division::where('id', $division_id)->get(),
                    'subjects' => Subject::all(),
                    'districts' => District::where('division_id', '=', $division_id)->get(),
                    'schools' => School::select('tbl_schools.name as name', 'tbl_schools.id as id')->join('tbl_districts', 'tbl_schools.district_id', 'tbl_districts.id')
                        ->where('division_id', $division_id)->get(),
                ];
                break;

            case 'School Head':
                $options = [
                    'subjects' => Subject::all(),
                    'schools' => School::select('tbl_schools.name as name', 'tbl_schools.id as id')
                        ->join('tbl_school_supervisors', 'tbl_schools.id', 'tbl_school_supervisors.school_id')
                        ->where('user_id', Auth::user()->id)->get(),
                ];
                break;
        }

        return $options;
    }

    // NERRIE'S NOTE:
    // This function is used globally
    // This will return grade levels available in school by passing school_id in the parameter(s)
    // EX : if school type is elementary - will return Grade 1 - 6 and Kinder
    public static function filterGradeLevel($school_id)
    {

        $school = School::find($school_id);
        $school_type = SchoolType::find($school->school_type_id);
        $filter = [];

        switch ($school_type->type) {
            case 'Elementary':
                $filter = [
                    'Kinder',
                    'Grade 1',
                    'Grade 2',
                    'Grade 3',
                    'Grade 4',
                    'Grade 5',
                    'Grade 6',
                ];
                break;
            case 'Junior High School':
                $filter = [
                    'Grade 7',
                    'Grade 8',
                    'Grade 9',
                    'Grade 10',
                ];
                break;
            case 'Stand Alone Senior High':
                $filter = [
                    'Grade 11',
                    'Grade 12',
                ];
                break;
            case 'Integrated':
                $filter = [
                    'Grade 7',
                    'Grade 8',
                    'Grade 9',
                    'Grade 10',
                    'Grade 11',
                    'Grade 12',
                ];
                break;
            case 'ALS':
                $filter = [
                    'Kinder',
                    'Grade 1',
                    'Grade 2',
                    'Grade 3',
                    'Grade 4',
                    'Grade 5',
                    'Grade 6',
                    'Grade 7',
                    'Grade 8',
                    'Grade 9',
                    'Grade 10',
                    'Grade 11',
                    'Grade 12',
                ];
                break;
            default:
                // code...
                break;
        }

        return $filter;
    }

    /* NERRIE'S NOTE:
    This function is used globally
    This will return list of Classrooms with Details with the ff. structure :
    array:1 [▼
        "Kinder" => array:2 [▼
            0 => array:7 [▼
            "classroom_id" => 149
            "section" => "FAITH"
            "section_id" => 120
            "advisor" => "MICHELLE RUTA"
            "subject" => "Mathematics"
            "classes" => 1
            "is_advisory" => 1
            ]
        ]
    ] */

    public static function getClassroomsByTeacherUserId($id)
    {
        $classrooms = [];
        $teacher = Teacher::where('user_id', $id)->first();

        if (! $teacher) {
            return $classrooms;
        }

        $school_id = $teacher->school_id;

        $academic_year = AcademicYear::active();

        if (! $academic_year) {
            return $classrooms;
        }

        $grade_level_filter = CustomFunction::filterGradeLevel($school_id);
        $grade_levels = GradeLevel::whereIn('level', $grade_level_filter)->get()->keyBy('id');

        $classrooms_query = Classroom::with([
            'section:id,section',
            'advisoryTeacherClass.teacher.user.person',
            'advisoryTeacherClass.subject',
        ])
            ->where('school_id', $school_id)
            ->where('academic_year_id', $academic_year->id)
            ->whereIn('grade_level_id', $grade_levels->keys());

        if (Auth::user()->classification == 'Teacher') {
            $current_teacher = Auth::user()->id == $id
                ? $teacher
                : Teacher::where('user_id', Auth::user()->id)->first();

            if (! $current_teacher) {
                return $classrooms;
            }

            $classrooms_query->withCount([
                'teacherClasses as current_teacher_classes_count' => function ($query) use ($current_teacher) {
                    $query->where('teacher_id', $current_teacher->id);
                },
                'teacherClasses as current_teacher_advisory_count' => function ($query) use ($current_teacher) {
                    $query->where('teacher_id', $current_teacher->id)
                        ->where('advisory', 1);
                },
            ]);
        }

        $rooms_by_grade_level = $classrooms_query->get()->groupBy('grade_level_id');

        foreach ($grade_levels as $key => $grade_level) {

            foreach ($rooms_by_grade_level->get($grade_level->id, collect()) as $room) {

                $is_advisory = 0;
                $classes = 0;
                $is_included = 1;

                if (Auth::user()->classification == 'Teacher') {
                    $classes = (int) ($room->current_teacher_classes_count ?? 0);
                    $is_advisory = ((int) ($room->current_teacher_advisory_count ?? 0) > 0) ? 1 : 0;

                    if ($is_advisory == 0 && $classes == 0) {
                        $is_included = 0;
                    }
                }

                $advisory = $room->advisoryTeacherClass;
                $section = $room->section;
                $advisor_person = $advisory?->teacher?->user?->person;
                $subject = $advisory?->subject;

                if ($advisory && (! $advisor_person || ! $subject)) {
                    $is_included = 0;
                }

                if ($is_included == 1) {
                    $room_details = [
                        'classroom_id' => $room->id,
                        'section' => $section?->section,
                        'section_id' => $section?->id,
                        'advisor' => ($advisory && $advisor_person) ? $advisor_person->first_name.' '.$advisor_person->last_name : 'N/A',
                        'subject' => ($advisory && $subject) ? $subject->title : 'N/A',
                        'classes' => $classes,
                        'is_advisory' => $is_advisory,
                        'grade_level' => $grade_level->level,
                        'school_year' => $academic_year?->from.'-'.$academic_year->to,
                        'teacher_class_id' => $advisory?->id,
                    ];
                    $classrooms[$grade_level->level][] = $room_details;
                }
            }
        }

        return $classrooms;
    }

    public static function getClassrooms()
    {

        $classrooms = [];
        if (Auth::user()->classification == 'School Head') {
            $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        } else {
            $school_id = Teacher::where('user_id', Auth::user()->id)->value('school_id');
        }

        // $school = School::find($school_id);
        $academic_year = AcademicYear::active();
        $grade_level_filter = CustomFunction::filterGradeLevel($school_id);
        $grade_levels = GradeLevel::whereIn('level', $grade_level_filter)->get();

        foreach ($grade_levels as $key => $grade_level) {

            $get_rooms = Classroom::where('grade_level_id', $grade_level->id)
                ->where('school_id', $school_id)
                ->where('academic_year_id', $academic_year->id)
                ->get();

            foreach ($get_rooms as $key => $room) {

                $is_advisory = 0;
                $classes = 0;
                $is_included = 1;

                if (Auth::user()->classification == 'Teacher') {

                    $current_teacher = Teacher::where('user_id', Auth::user()->id)->first();
                    $classes = TeacherClass::where([
                        'classroom_id' => $room->id,
                        'teacher_id' => $current_teacher->id,
                    ])->get();
                    $classes = $classes->count();

                    $check_advisory = TeacherClass::where([
                        'classroom_id' => $room->id,
                        'advisory' => 1,
                        'teacher_id' => $current_teacher->id,
                    ])->first();

                    $is_advisory = ($check_advisory) ? 1 : $is_advisory;

                    if ($is_advisory == 0 && $classes == 0) {
                        $is_included = 0;
                    }
                }

                $room = Classroom::find($room->id);
                $advisory = TeacherClass::where([
                    'classroom_id' => $room->id,
                    'advisory' => 1,
                ])->first();

                if ($advisory) {
                    $teacher = Teacher::find($advisory->teacher_id);
                    $subject = Subject::find($advisory->subject_id);
                    $user = User::find($teacher->user_id);
                    if ($user) {
                        $person = Person::find($user->person_id);
                    } else {
                        // if teacher is deleted
                        $is_included = 0;
                    }
                }

                if ($is_included == 1) {
                    $section = Section::find($room->section_id);
                    $room_details = [
                        'classroom_id' => $room->id,
                        'section' => $section->section,
                        'section_id' => $section->id,
                        'advisor' => ($advisory) ? $person->first_name.' '.$person->last_name : 'N/A',
                        'subject' => ($advisory) ? $subject->title : 'N/A',
                        'classes' => $classes,
                        'is_advisory' => $is_advisory,
                        'grade_level' => $grade_level->level,
                        'teacher_class_id' => $advisory->id,
                    ];
                    $classrooms[$grade_level->level][] = $room_details;
                }
            }
        }

        return $classrooms;
    }

    // NERRIE'S NOTE
    // This function is used globally
    // This will return Student with Details inside a Classroom
    // These students are mainly from uploaded SF1
    public static function getStudentsPerClassroom($classroom_id)
    {
        $students = Student::select(
            'tbl_students.id',
            'lrn',
            'tbl_student_classrooms.status',
            'first_name',
            'middle_name',
            'last_name',
            'birth_date',
            'gender',
            'is_uploaded',
            'tbl_classrooms.section_id as sectionId',
            'tbl_classrooms.grade_level_id as gradeLevelId',
            'tbl_classrooms.id as classroomId'
        )->join('tbl_student_classrooms', 'tbl_students.id', 'tbl_student_classrooms.student_id')
            ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->join('tbl_classrooms', 'tbl_student_classrooms.classroom_id', 'tbl_classrooms.id')
            ->where('tbl_student_classrooms.classroom_id', $classroom_id)
            ->where('tbl_student_classrooms.status', 1)
            ->get();

        return $students;
    }

    // NERRIE'S NOTE
    // This is a custom validation
    // This will return error message
    // These students are mainly from uploaded SF1
    public static function getTeacherUploaderError($data, $key)
    {

        $error = [];
        $row = $key - 1;
        $is_email_exist = Teacher::where('email', $data['email'])->get();
        $is_user_exist = User::where('username', $data['email'])->get();

        // ($data['birth_date'] == null ) ? $error[] = 'Error on row '.$row.' : Birthday cannot be null' : '';
        ($data['first_name'] == null) ? $error[] = 'Error on row '.$row.' : First Name cannot be null' : '';
        ($data['last_name'] == null) ? $error[] = 'Error on row '.$row.' : Last Name cannot be null' : '';
        ($data['email'] == null) ? $error[] = 'Error on row '.$row.' : Email cannot be null' : '';
        ($data['gender'] == null) ? $error[] = 'Error on row '.$row.' : Gender cannot be null' : '';
        ($is_email_exist->count() > 0) ? $error[] = 'Error on row '.$row.' : Email already exist.' : '';
        ($is_user_exist->count() > 0) ? $error[] = 'Error on row '.$row.' : Username already taken.' : '';

        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];

        if ($data['birth_date'] != null) {
            $array_birthdate = explode(' ', $data['birth_date']);
            if (count($array_birthdate) == 3) {
                if (in_array($array_birthdate[0], $months)) {
                } else {
                    $error[] = 'Error on row '.$row.' : Invalid Month Format in Birthdate.';
                }
                $day = str_replace(',', '', $array_birthdate[1]);
                if ($day >= 1 && $day <= 31) {
                } else {
                    $error[] = 'Error on row '.$row.' : Invalid Day Format in Birthdate.';
                }
                if (strlen($array_birthdate[2]) != 4) {
                    $error[] = 'Error on row '.$row.' : Invalid Year Format in Birthdate.';
                }
            } else {
                $error[] = 'Error on row '.$row.' : Invalid Birthdate Format.';
            }
        }

        return $error;
    }

    public static function getSchoolHeadUploaderError($data, $key)
    {

        $error = [];
        $row = $key - 1;
        $is_email_exist = SchoolSupervisor::where('email', $data['email'])->get();
        $is_user_exist = User::where('username', $data['email'])->get();

        ($data['first_name'] == null) ? $error[] = 'Error on row '.$row.' : First Name cannot be null' : '';
        ($data['last_name'] == null) ? $error[] = 'Error on row '.$row.' : Last Name cannot be null' : '';
        ($data['email'] == null) ? $error[] = 'Error on row '.$row.' : Email cannot be null' : '';
        // ($data['birth_date'] == null ) ? $error[] = 'Error on row '.$row.' : Birthday cannot be null' : '';
        ($data['gender'] == null) ? $error[] = 'Error on row '.$row.' : Gender cannot be null' : '';
        ($data['school_id'] == null) ? $error[] = 'Error on row '.$row.' : School ID cannot be null' : '';
        ($is_email_exist->count() > 0) ? $error[] = 'Error on row '.$row.' : Email already exist.' : '';
        ($is_user_exist->count() > 0) ? $error[] = 'Error on row '.$row.' : Username already exist.' : '';

        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];

        if ($data['birth_date'] != null) {
            $array_birthdate = explode(' ', $data['birth_date']);
            if (count($array_birthdate) == 3) {
                if (in_array($array_birthdate[0], $months)) {
                } else {
                    $error[] = 'Error on row '.$row.' : Invalid Month Format in Birthdate.';
                }
                $day = str_replace(',', '', $array_birthdate[1]);

                if ($day >= 1 && $day <= 31) {
                } else {
                    $error[] = 'Error on row '.$row.' : Invalid Day Format in Birthdate.';
                }
                if (strlen($array_birthdate[2]) != 4) {
                    $error[] = 'Error on row '.$row.' : Invalid Year Format in Birthdate.';
                }
            } else {
                $error[] = 'Error on row '.$row.' : Invalid Birthdate Format.';
            }
        }

        if ($data['school_id'] != null) {
            $is_school_exist = School::where('code', $data['school_id'])->get();
            if ($is_school_exist->count() == 0) {
                $error[] = 'Error on row '.$row.' : School code '.$data['school_id'].' not found.';
            }
        }

        return $error;
    }

    public static function getAssessmentDetails($assessment_id)
    {

        $assessment = Assessment::select(
            'tbl_assessments.title as assessment',
            'tbl_assessments.id as id',
            'tbl_assessments.number_of_items',
            'tbl_assessments.date',
            'tbl_assessments.academic_year_id',
            'from',
            'to',
            'level',
            'tbl_subjects.title as subject',
            'type',
            'period'
        )
            ->join('tbl_grade_levels', 'tbl_assessments.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_subjects', 'tbl_assessments.subject_id', 'tbl_subjects.id')
            ->join('tbl_periods', 'tbl_assessments.period_id', 'tbl_periods.id')
            ->join('tbl_assessment_types', 'tbl_assessments.assessment_type_id', 'tbl_assessment_types.id')
            ->join('tbl_academic_years', 'tbl_assessments.academic_year_id', 'tbl_academic_years.id')
            ->where('tbl_assessments.id', $assessment_id)
            ->first();

        return $assessment;
    }

    public static function getAssessmentsDetailsByTeacherId($teacher_id)
    {
        $assessment = Assessment::select(
            'tbl_assessments.title as assessment',
            'tbl_assessments.id as id',
            'tbl_assessments.number_of_items',
            'tbl_assessments.date',
            'from',
            'to',
            'level',
            'tbl_subjects.title as subject',
            'type',
            'period'
        )
            ->join('tbl_grade_levels', 'tbl_assessments.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_subjects', 'tbl_assessments.subject_id', 'tbl_subjects.id')
            ->join('tbl_periods', 'tbl_assessments.period_id', 'tbl_periods.id')
            ->join('tbl_assessment_types', 'tbl_assessments.assessment_type_id', 'tbl_assessment_types.id')
            ->join('tbl_academic_years', 'tbl_assessments.academic_year_id', 'tbl_academic_years.id')
            ->where('tbl_assessments.teacher_id', $teacher_id)
            ->get();

        return $assessment;
    }

    public static function getClassDetails($class_id = null, $teacherUserId = null)
    {
        $class = TeacherClass::select(
            'tbl_teacher_classes.id as id',
            'first_name',
            'middle_name',
            'last_name',
            'suffix',
            'level',
            'section',
            'title as subject'
        )->join('tbl_teachers', 'tbl_teacher_classes.teacher_id', 'tbl_teachers.id')
            ->join('tbl_users', 'tbl_teachers.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_subjects', 'tbl_teacher_classes.subject_id', 'tbl_subjects.id')
            ->when($class_id, function($q) use ($class_id) {
                $q->where('tbl_teacher_classes.id', $class_id);
            })
            ->when($teacherUserId, function($q) use ($teacherUserId) {
                $q->where('tbl_teachers.user_id', $teacherUserId);
            })
            ->first();

        return $class;
    }

    public static function getClassroomDetails($classroom_id)
    {
        $select = [
            'tbl_classrooms.id as id',
            'tbl_sections.section',
            'tbl_grade_levels.level',
            'tbl_academic_years.from',
            'tbl_academic_years.to',
            'tbl_schools.name',
        ];
        $query = Classroom::join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_academic_years', 'tbl_classrooms.academic_year_id', 'tbl_academic_years.id')
            ->join('tbl_schools', 'tbl_classrooms.school_id', 'tbl_schools.id')
            ->where('tbl_classrooms.id', $classroom_id);

        $result = $query->first();
        if ($result->level == 'Grade 11' || $result->level == 'Grade 12') {

            $query = $query->join('tbl_strands', 'tbl_classrooms.strand_id', 'tbl_strands.id')
                ->join('tbl_tracks', 'tbl_classrooms.track_id', 'tbl_tracks.id')
                ->join('tbl_semesters', 'tbl_classrooms.semester_id', 'tbl_semesters.id');

            array_push(
                $select,
                'tbl_tracks.name as track',
                'tbl_strands.name as strand',
                'tbl_semesters.semester',
            );
        }

        $result = $query->select($select)->first();

        return $result;
    }

    public static function getAssessmentKeys($assessment_id)
    {

        $get_assessment_keys = AssessmentKey::select(
            'item_number',
            'assignment'
        )->join('tbl_assessment_options', 'tbl_assessment_keys.id', 'tbl_assessment_options.assessment_key_id')
            ->join('tbl_options', 'tbl_assessment_options.option_id', 'tbl_options.id')
            ->where('is_correct', 1)
            ->where('assessment_id', $assessment_id)
            ->get();

        $assessment_keys = [];

        foreach ($get_assessment_keys as $key) {
            $assessment_keys[$key->item_number] = $key->assignment;
        }

        return $assessment_keys;
    }

    public static function updateAssessmentResult($class_assessment_id)
    {

        $class_assessment = ClassAssessment::find($class_assessment_id);
        $assessment = Assessment::find($class_assessment->assessment_id);

        $highest = StudentScore::select(DB::raw('MAX(score) as score'))
            ->where('class_assessment_id', $class_assessment_id)
            ->first();

        $lowest = StudentScore::select(DB::raw('MIN(score) as score'))
            ->where('class_assessment_id', $class_assessment_id)
            ->first();

        $range = $highest->score - $lowest->score;
        $part = floor($range * .27);

        $LPG = $lowest->score + $part;
        $HPG = $highest->score - $part;

        $student_scores = StudentScore::with(
            'student:id,lrn,user_id',
            'student.user',
            'student.studentAnswers',
            'student.user.person:id,first_name,middle_name,last_name,birth_date,gender'
        )
            ->where('class_assessment_id', $class_assessment_id)
            ->get();

        $result = [];

        foreach ($student_scores as $key => $student_score) {

            $percentage = ($student_score->score / $assessment->number_of_items) * (100);
            $percentage = number_format((float) $percentage, 2, '.', '');

            // PROFICIENCY LEVEL
            // score = 17
            // LP = 17
            // HP = 38

            if ($student_score->score < $LPG) {
                $proficiency = 'LP';
            } elseif ($student_score->score >= $LPG && $student_score->score < $HPG) {
                $proficiency = 'AP';
            } elseif ($student_score->score >= $HPG) {
                $proficiency = 'HP';
            }

            // ACHIEVEMENT LEVEL
            if ($percentage >= 0 && $percentage <= 5) {
                $achievement = 'ANM';
            } elseif ($percentage >= 6 && $percentage <= 15) {
                $achievement = 'VL';
            } elseif ($percentage >= 16 && $percentage <= 35) {
                $achievement = 'L';
            } elseif ($percentage >= 36 && $percentage <= 65) {
                $achievement = 'AVR';
            } elseif ($percentage >= 66 && $percentage <= 85) {
                $achievement = 'MTM';
            } elseif ($percentage >= 86 && $percentage <= 95) {
                $achievement = 'CAM';
            } elseif ($percentage >= 96 && $percentage <= 100) {
                $achievement = 'M';
            }

            $get_answers = StudentAnswer::where([
                'student_id' => $student_score->student_id,
                'class_assessment_id' => $class_assessment_id,
            ])->get();

            $answers = [];

            foreach ($get_answers as $key => $get_answer) {
                $answers[$get_answer->item_number] = [
                    'answer' => $get_answer->answer,
                    'is_correct' => $get_answer->is_correct,
                ];
            }

            $result[$student_score->student_id] = [
                'lrn' => $student_score->student->lrn,
                'name' => $student_score->student->user?->person?->last_name.', '.$student_score->student->user?->person?->first_name.' '.$student_score->student->user?->person?->middle_name,
                'gender' => $student_score->student->user?->person?->gender,
                'score' => $student_score->score,
                'percentage' => $percentage,
                'proficiency' => $proficiency,
                'achievement' => $achievement,
                'answers' => $answers,
            ];
        }           
        Storage::disk('public')->put('res-'.$class_assessment_id.'.json', json_encode($result));

        return $result;
    }

    public static function verifyAnswerKeys($spreadsheet)
    {

        $cell = [

            'title' => 'C1',
            'subject' => 'C2',
            'date' => 'C3',
            'type' => 'C4',
            'items' => 'C5',
            'section' => 'E3',
            'grade' => 'E4',
            'period' => 'E5',
            'semester' => 'K2',
            'track' => 'K3',
            'strand' => 'K4',
            'course' => 'K5',
            'item_no' => 'A',
            'answer' => 'B',
            'competency' => 'C',
            'question' => 'D',
            'choice_a' => 'F',
            'choice_b' => 'G',
            'choice_c' => 'H',
            'choice_d' => 'I',
            'start' => 9,

        ];
            
        //TODO: change getActiveSheet to getSheetByName('Answer Keys')
        $assessment = [
            'title' => $spreadsheet->getActiveSheet()->getCell($cell['title'])->getValue(),
            'subject' => $spreadsheet->getActiveSheet()->getCell($cell['subject'])->getValue(),
            'date' => $spreadsheet->getActiveSheet()->getCell($cell['date'])->getValue(),
            'type' => $spreadsheet->getActiveSheet()->getCell($cell['type'])->getValue(),
            'items' => $spreadsheet->getActiveSheet()->getCell($cell['items'])->getValue(),
            'section' => $spreadsheet->getActiveSheet()->getCell($cell['section'])->getValue(),
            'grade' => $spreadsheet->getActiveSheet()->getCell($cell['grade'])->getValue(),
            'period' => $spreadsheet->getActiveSheet()->getCell($cell['period'])->getValue(),
            'semester' => $spreadsheet->getActiveSheet()->getCell($cell['semester'])->getValue(),
            'track' => $spreadsheet->getActiveSheet()->getCell($cell['track'])->getValue(),
            'course' => $spreadsheet->getActiveSheet()->getCell($cell['course'])->getValue(),
            'strand' => $spreadsheet->getActiveSheet()->getCell($cell['strand'])->getValue(),
            'keys' => [],
        ];

        $error = [];
        $teacher_id = Teacher::where('user_id', Auth::user()->id)->value('id');

        $grade_level = GradeLevel::where('level', $assessment['grade'])->get();
        $subject = Subject::where('title', $assessment['subject'])->get();
        $period = Period::where('period', $assessment['period'])->get();
        $type = AssessmentType::where('type', $assessment['type'])->get();
        $academic_year = AcademicYear::active();

        $grade_level_id = null;
        $subject_id = null;
        $period_id = null;
        $type_id = null;

        if ($grade_level->count() == 0) {
            $error[] = 'Invalid Grade Level.';
        } else {
            $grade_level_id = $grade_level[0]->id;
        }

        if ($subject->count() == 0) {
            $error[] = 'Invalid Grade Level.';
        } else {
            $subject_id = $subject[0]->id;
        }

        if ($period->count() == 0) {
            $error[] = 'Invalid Period.';
        } else {
            $period_id = $period[0]->id;
        }

        if ($type->count() == 0) {
            $error[] = 'Invalid Assessment Type.';
        } else {
            $type_id = $type[0]->id;
        }

        if ($subject_id != null && $grade_level_id != null) {

            $classes = TeacherClass::where('teacher_id', $teacher_id)
                ->select(
                    'tbl_teacher_classes.id as id',
                    'level',
                    'title'
                )->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', 'tbl_classrooms.id')
                ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
                ->join('tbl_subjects', 'tbl_teacher_classes.subject_id', 'tbl_subjects.id')
                ->where('tbl_teacher_classes.subject_id', $subject_id)
                ->where('tbl_classrooms.grade_level_id', $grade_level_id)
                ->get();

            if ($classes->count() == 0) {
                $error[] = 'You have no class of '.$assessment['grade'].' - '.$assessment['subject'];
            }

            if ($period_id != null && $type_id != null) {

                $term_exam_type_id = AssessmentType::where('type', 'Term Exam')->value('id');

                if ($type_id == $term_exam_type_id) {
                    $existing_assessment = Assessment::where([
                        'grade_level_id' => $grade_level_id,
                        'subject_id' => $subject_id,
                        'period_id' => $period_id,
                        'academic_year_id' => $academic_year->id,
                        'assessment_type_id' => $type_id,
                        'teacher_id' => $teacher_id,
                    ])->get();

                    if ($existing_assessment->count() > 0) {
                        $error[] = 'Assessment already exists.';
                    }
                }
            }
        }

        if (count($error) == 0) {

            $assessment['grade'] = $grade_level_id;
            $assessment['subject'] = $subject_id;
            $assessment['period'] = $period_id;
            $assessment['type'] = $type_id;

            for ($x = $cell['start']; $x <= $assessment['items'] + $cell['start'] - 1; $x++) {

                $code = $spreadsheet->getActiveSheet()->getCell($cell['competency'].$x)->getValue();
                $item_no = $spreadsheet->getActiveSheet()->getCell($cell['item_no'].$x)->getValue();
                $check_competency = Competency::where('code', 'LIKE', '%'.$code.'%')
                    ->where('grade_level_id', $grade_level_id)
                    ->where('subject_id', $subject_id)
                    ->get();

                if ($check_competency->count() == 0) {
                    $error[] = 'Error on Item no. '.$item_no.' : Invalid Code - '.$code;
                } else {
                    $assessment['keys'][$item_no] = [
                        'item_no' => $spreadsheet->getActiveSheet()->getCell($cell['item_no'].$x)->getValue(),
                        'answer' => $spreadsheet->getActiveSheet()->getCell($cell['answer'].$x)->getValue(),
                        'competency' => $check_competency[0]->id,
                        'question' => [
                            'Q' => $spreadsheet->getActiveSheet()->getCell($cell['question'].$x)->getValue(),
                            'A' => $spreadsheet->getActiveSheet()->getCell($cell['choice_a'].$x)->getValue(),
                            'B' => $spreadsheet->getActiveSheet()->getCell($cell['choice_b'].$x)->getValue(),
                            'C' => $spreadsheet->getActiveSheet()->getCell($cell['choice_c'].$x)->getValue(),
                            'D' => $spreadsheet->getActiveSheet()->getCell($cell['choice_d'].$x)->getValue(),
                        ], // end of question array()
                    ];
                }
            } // end foreach
        }

        if (count($error) == 0) {
            return $assessment;
        } else {
            return $error;
        }
    }
}
