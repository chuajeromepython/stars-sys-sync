<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


use Auth;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassAssessment;
use App\Models\CustomFunction;
use App\Models\Course;
use App\Models\GradeLevel;
use App\Models\Person;
use App\Models\SchoolSupervisor;
use App\Models\School;
use App\Models\Semester;
use App\Models\Section;
use App\Models\Student;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\Track;
use App\Models\Teacher;
use App\Models\TeacherClass;
use App\Models\User;


class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = [
            'name'      =>  'Classroom',
            'title'     =>  'Classroom Management',
            'crumb'     =>  array('Classrooms' => '/classrooms')
        ];

        
        
        $academic_year = AcademicYear::active();
        if($academic_year == null){
            return back()->withErrors("No Active Academic Year. Please Contact the Division Administrator.");
        }

        $school_id = (Auth::user()->classification == "Teacher")
            ? Teacher::where('user_id', Auth::user()->id)->value('school_id')
            : SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');

        if(Auth::user()->classification == "School Head"){
            $message = 'You can create new classroom by clicking "Add Classroom" or "Upload Classroom"';
        }else{
            $message = 'You can contact your School Head for Classroom Management.';
        }

        $classrooms = CustomFunction::getClassrooms();
       
        return view('classrooms.index', compact(
            'page', 'classrooms', 'message', 'school_id'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         $page = [
            'name'      =>  'Classroom',
            'title'     =>  'Add Classroom',
            'crumb'     =>  array('Classrooms' => '/classrooms', 'Add Classroom' => '/classrooms/create')
        ];


        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $school = School::find($school_id);
        $grade_level_filter = CustomFunction::filterGradeLevel($school_id);
        $grade_levels = GradeLevel::whereIn('level', $grade_level_filter)->get();
        $sections = Section::where('school_id', $school_id)->get();
        $subjects = Subject::all();
        $academic_year = AcademicYear::active();

        $teachers = User::select(
                'tbl_teachers.id', 'username', 'user_id',
                'first_name', 'middle_name', 'last_name', 'suffix'
            )->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('classification', "Teacher")
            ->where('school_id', $school_id)
            ->get();

        $tracks = Track::all();
        $semesters = Semester::all();

        if($academic_year == null){
            return back()->withErrors("No Active Academic Year. Please Contact the Division Administrator.");
        }
       
        return view('classrooms.create', compact(
            'page', 'school', 'grade_levels', 'sections',
            'academic_year', 'teachers', 'subjects', 'tracks', 'semesters'
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

        $academic_year = AcademicYear::active();
        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');

        $existing = Classroom::where('academic_year_id', $academic_year->id)
            ->where('grade_level_id', $request->grade_level_id)
            ->where('section_id', $request->section_id);

        if($request->grade_level_id == 11 || $request->grade_level_id == 12 ){
            if($request->track_id){
                $existing = $existing->where('track_id', $request->track_id);
            }
            if($request->strand_id){
                $existing = $existing->where('strand_id', $request->strand_id);
            }
            if($request->course_id){
                $existing = $existing->where('course_id', $request->course_id);
            }
            if($request->semester_id){
                $existing = $existing->where('semester_id', $request->semester_id);
            }
        }
        $existing = $existing->get();
        

        if($existing->count() == 0){
            
            DB::beginTransaction();
            try {

                $classroom = new Classroom;
                $classroom->school_id = $school_id;
                $classroom->academic_year_id = $academic_year->id;
                $classroom->grade_level_id = $request->grade_level_id;
                $classroom->section_id = $request->section_id;
                $classroom->track_id = ($request->track_id) ? ($request->track_id) : null;
                $classroom->strand_id = ($request->strand_id) ? ($request->strand_id) : null;
                $classroom->course_id = ($request->course_id) ? ($request->course_id) : null;
                $classroom->semester_id = ($request->semester_id) ? ($request->semester_id) : null;
                $classroom->save();


                foreach ($request->teachers as $key => $teacher) {
                    $class = new TeacherClass;
                    $class->classroom_id = $classroom->id;
                    $class->teacher_id = $request->teachers[$key];
                    $class->subject_id = $request->subjects[$key];
                    $class->advisory = ($key == 0) ? true : false;
                    $class->save();

                }

                DB::commit();
                $result = true;

            } catch (Exception $e) {
                DB::rollBack();
                $result = $e->getMessage();
            }

            if($result === true) {
                return back()->with('success', 'New classroom has been added successfully.');
            } else {
                return back()->withErrors($result);
            }

        }else{
            return back()->withErrors("Classroom already exists.");
        }


        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Classroom  $classroom
     * @return \Illuminate\Http\Response
     */
    public function show(Classroom $classroom)
    {
        $page = [
            'name'      =>  'Classroom',
            'title'     =>  'Classroom Subject Classes',
            'crumb'     =>  array('Classrooms' => '/classrooms', 'Subject Classes' => '')
        ];

        $is_advisory = 0;
        $track = null;
        $strand = null;
        $course = null;
        $semester = null;

        if($classroom->grade_level_id == 11 || $classroom->grade_level_id == 12){
            $track = Track::find($classroom->track_id);
            $strand = Strand::find($classroom->strand_id);
            $course = Course::find($classroom->course_id);
            $semester = Semester::find($classroom->semester_id);
        }

        
        $classroom = Classroom::select(
                'tbl_classrooms.id as id','level','section', 'tbl_classrooms.*'
            )->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->find($classroom->id);


        $academic_year = AcademicYear::active();
        $classes = TeacherClass::select(
            'tbl_teacher_classes.id as id', 'advisory',
            'title', 'first_name', 'middle_name', 'last_name', 'suffix'
        )->join('tbl_teachers', 'tbl_teacher_classes.teacher_id', 'tbl_teachers.id')
        ->join('tbl_users', 'tbl_teachers.user_id', 'tbl_users.id')
        ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
        ->join('tbl_subjects', 'tbl_teacher_classes.subject_id', 'tbl_subjects.id')
        ->where('classroom_id', $classroom->id);
        
        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $teachers = User::select(
            'tbl_teachers.id', 'username', 'user_id',
            'first_name', 'middle_name', 'last_name', 'suffix'
        )->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
        ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
        ->where('classification', "Teacher")
        ->where('school_id', $school_id)
        ->get();

        if(Auth::user()->classification == "Teacher"){

            $current_teacher = Teacher::where('user_id', Auth::user()->id)->first();
            $check_advisory = TeacherClass::where([
                "classroom_id" => $classroom->id,
                "advisory" => 1,
                "teacher_id" => $current_teacher->id
            ])->first();

            $is_advisory = ($check_advisory) ? 1 : $is_advisory;
            
            if($is_advisory == 0){
                $classes = $classes->where('teacher_id', $current_teacher->id);
            }

        }
        $classes = $classes->get();
        $students = CustomFunction::getStudentsPerClassroom($classroom->id);


        $page['title'] = $is_advisory ? 'Classroom Advisory Class' : 'Classroom Subject Class';
        $subjects = Subject::all();

        return view('classrooms.show', compact(
            'page', 'classroom', 'classes',
            'track', 'strand', 'course', 'semester',
            'academic_year', 'is_advisory', 'students',
            'subjects', 'teachers'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Classroom  $classroom
     * @return \Illuminate\Http\Response
     */
    public function edit(Classroom $classroom)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Classroom  $classroom
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

       

        $classroom = Classroom::find($request->classroom_id);
        $is_existing = Classroom::where([
            "grade_level_id" => $classroom->grade_level_id,
            "school_id" => $classroom->school_id,
            "section_id" => $request->section_id,
        ])->where('id', '<>', $classroom->id)
        ->get();

        if ($is_existing->count() > 0) {
            return back()->withErrors("Classroom already exist!");
        }else{
            $classroom->section_id = $request->section_id;
            $classroom->save();

            $class = TeacherClass::where('advisory', 1)
                ->where('classroom_id', $request->classroom_id)
                ->first();


            $class->teacher_id = $request->teacher_id;
            $class->save();


            return redirect('/classrooms')->with('success', 'Classroom has been updated successfully.');
        }
       

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Classroom  $classroom
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   
        $classroom = Classroom::find($request->classroom_id);
        $has_record = ClassAssessment::join('tbl_teacher_classes', 'tbl_teacher_classes.id', 'tbl_class_assessments.class_id')
        ->where('classroom_id', $classroom->id)
        ->get();
        if($has_record->count() > 0){
            return back()->withErrors('Classroom contains assessment.');
        }else{
            $classroom->delete();
            return back()->with('success', 'Classroom has been deleted successfully.');
        }
    }

    public function upload(Request $request){

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240'
        ], [
            'file.required' => 'Please upload a file.',
            'file.mimes' => 'The file must be an Excel file (xlsx or xls).',
            'file.max' => 'The file size must not exceed 10MB.'
        ]);

        $spreadsheet = IOFactory::load( $request->file('file') );
        $sheet = $spreadsheet->getActiveSheet()->toArray();
        $school_id = SchoolSupervisor::where('user_id', Auth::user()->id)->value('school_id');
        $grade_level_filter = CustomFunction::filterGradeLevel($school_id);
        $grade_levels = GradeLevel::whereIn('level', $grade_level_filter)->get();
        $academic_year = AcademicYear::active();
        $errors = array();
        $error_messages = array();

        DB::beginTransaction();
        try {
            $title = $spreadsheet->getActiveSheet()->getCell('A1');
            $is_shs = ($title == "SHS - ADVISORY CLASS UPLOADER") ? 1 : 0;

            foreach ($sheet as $key => $row) {



                if ($key > 1) {

                    if ($row[0] == null) { break; }

                    $line = $key-1;
                    $error = array();
                    $grade_level_id = null;
                    $section_id = null;
                    $subject_id = null;

                    $data = array(
                        "email" => $row[0],
                        "grade_level" => $row[1],
                        "section" => $row[2]
                    );

                    $data["subject"] =  ($is_shs == 1) ? $row[5] : $row[3];

                    $teacher = Teacher::where('email', $data['email'])
                        ->where('school_id', $school_id)->get();

                    if($teacher->count() == 0){
                        $error[] = 'Error on row '.$line.' : Email not found - '.$data['email'];
                    }

                    if($data['grade_level'] == null){
                        $error[] = 'Error on row '.$line.' : Grade Level cannot be null.';
                    }else{
                        $grade_level = GradeLevel::where('level', $data['grade_level'])->get();
                        if($grade_level->count() == 0){
                            $error[] = 'Error on row '.$line.' : Grade Level not found.';
                        }else{
                            // Check if grade level is valid for school type - 10/07/22
                            $is_grade_valid = array_search($grade_level[0]->id, 
                                array_column($grade_levels->toArray(), 'id')
                            );
                            $is_grade_valid = 0; 
                            foreach ($grade_levels as $key => $gl) {
                                if ($gl->id == $grade_level[0]->id) {
                                    $is_grade_valid = 1; 
                                    break;
                                }
                            }
                            if ($is_grade_valid == 0) {
                                $error[] = 'Error on row '.$line.' : Grade Level is not valid for school type.'; 
                            }else{
                                $grade_level_id = $grade_level[0]->id;     
                            }
                        }
                    }

                    if($data['section'] != null){
                        $section = Section::where('section', $data['section'])
                            ->where('school_id', $school_id)->get();
                        if($section->count() == 0){
                            $error[] = 'Error on row '.$line.' : Section not found.';
                        }else{
                            $section_id = $section[0]->id;
                        }
                    }

                    if($data['subject'] == null){
                        $error[] = 'Error on row '.$line.' : Subject cannot be null.';
                    }else{
                        $subject = Subject::where('title', $data['subject'])->get();
                        if($subject->count() == 0){
                            $error[] = 'Error on row '.$line.' : Subject not found - '.$data['subject'];
                        }else{
                            $subject_id = $subject[0]->id;
                        }
                    }

                    if($grade_level_id != null && $section_id != null){
                        $is_classroom_exist = Classroom::where([
                            "grade_level_id" => $grade_level_id,
                            "section_id" => $section_id,
                            "school_id" => $school_id,
                            "academic_year_id" => $academic_year->id
                        ])->get();

                        if($is_classroom_exist->count() > 0){
                            $error[] = 'Error on row '.$line.' : Classroom already exists.';
                        }
                    }

                    if($is_shs == 1){

                        $data['track'] = $row[3];
                        $data['strand'] = $row[4];
                        $data['semester'] = $row[6];

                        
                        if($data['track'] == null){
                            $error[] = 'Error on row '.$line.' : Track cannot be null.';
                        }else{
                            $track = Track::where('name', $data['track'])->get();
                            if($track->count() == 0){
                                $error[] = 'Error on row '.$line.' : Invalid Track - '.$data['track'];
                            }
                        }
                        if($data['strand'] == null){
                            $error[] = 'Error on row '.$line.' : Strand cannot be null.';
                        }else{
                            $strand = Strand::where('name', $data['strand'])->get();
                            if($strand->count() == 0){
                                $error[] = 'Error on row '.$line.' : Invalid Strand - '.$data['track'];
                            }
                        }
                        if($data['semester'] == null){
                            $error[] = 'Error on row '.$line.' : Semester cannot be null.';
                        }else{
                            $semester = Semester::where('semester', trim($data['semester']))->get();
                            if($semester->count() == 0){
                                $error[] = 'Error on row '.$line.' : Invalid semester - '.$data['semester'];
                            }
                        }
                    }

                    if(sizeOf($error) > 0){
                        $errors[] = $error;
                    }else{

                        $classroom = new Classroom;
                        $classroom->school_id = $school_id;
                        $classroom->academic_year_id = $academic_year->id;
                        $classroom->grade_level_id = $grade_level[0]->id;
                        $classroom->section_id = $section[0]->id;
                        if($is_shs == 1){
                            $classroom->track_id = $track[0]->id;
                            $classroom->strand_id = $strand[0]->id;
                            $classroom->semester_id = $semester[0]->id;
                        }
                        $classroom->save(); 

                        $class = new TeacherClass;
                        $class->classroom_id = $classroom->id;
                        $class->teacher_id = $teacher[0]->id;
                        $class->subject_id = $subject[0]->id;
                        $class->advisory = true;
                        $class->save();
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
            return redirect('/classrooms')->with('success', 'Classroom Uploader has been uploaded successfully.');
        } else {
            return back()->withErrors($result);
        }


    }
}
