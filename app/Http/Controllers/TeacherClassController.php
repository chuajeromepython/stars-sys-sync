<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Auth;
use App\Models\Classroom;
use App\Models\TeacherClass;
use App\Models\School;
use App\Models\Teacher;
use App\Models\SchoolSupervisor;
use App\Models\Subject;
use App\Models\Student;
use App\Models\User;
use App\Models\StudentClassroom;
use App\Models\StudentClass;

class TeacherClassController extends Controller
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
    public function destroy(Request $request)
    {   
        $class = TeacherClass::find($request->teacher_class_id)->get();
        $has_record = ClassAssessment::where('class_id', $request->teacher_class_id)->get();

        if ($has_record->count() > 0) {
            return back()->withErrors('Subject Class contains assessment.');
        }else{
            $classroom->delete();
        }
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
        $is_subject_exist = TeacherClass::where('classroom_id', $request->classroom_id)
            ->where('subject_id', $request->subject_id)->get();

        if ($is_subject_exist->count() == 0) {
            $teacher_class = new TeacherClass;
            $teacher_class->classroom_id = $request->classroom_id; 
            $teacher_class->teacher_id = $request->teacher_id; 
            $teacher_class->subject_id = $request->subject_id; 
            $teacher_class->advisory = 0;
            $teacher_class->save();
            return back()->with("success", "Subject Class successfully added.");
        }else{
            return back()->withErrors("Subject Class already exists.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TeacherClass  $teacherClass
     * @return \Illuminate\Http\Response
     */
    public function show(TeacherClass $teacher_class)
    {   

        $subject = Subject::find($teacher_class->subject_id);
        $page = [
            'name'      =>  'Classroom',
            'title'     =>  $subject->title.' Subject Class ',
            'crumb'     =>  array(
                'Classrooms' => '/classroom', 
                $subject->title.' Subject Class' => '/classrooms/'.$teacher_class->classroom_id,
                'Data' => ''
            )
        ];

        $students = Student::select(
            'tbl_students.id', 'lrn', 'tbl_student_classes.status',
            'first_name', 'middle_name', 'last_name', 'birth_date', 'gender',
            'tbl_student_classes.id as student_class_id'
        )->join('tbl_student_classes', 'tbl_students.id', 'tbl_student_classes.student_id')
        ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
        ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
        ->where('tbl_student_classes.class_id', $teacher_class->id)
        ->where('tbl_student_classes.status', '>', 0)
        ->get();

        $students_classroom = StudentClassroom::where('classroom_id', $teacher_class->classroom_id)->get();

        $classroom = Classroom::select(
                'tbl_classrooms.id as id','level','section', 'tbl_classrooms.*'
            )->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->find($teacher_class->classroom_id);

        $teacher = User::select(
                'tbl_teachers.id', 'username', 'user_id',
                'first_name', 'middle_name', 'last_name', 'suffix'
            )->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('tbl_teachers.id', $teacher_class->teacher_id)
            ->first();

        $statuses = Student::getStatusOptions();

        return view('teacher_classes.show', compact(
            'page','students', 'students_classroom',
            'teacher_class', 'subject', 'teacher', 'classroom',
            'statuses'

        ));
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
        $errors = array();
        $error_messages = array();
        $classroom = Classroom::find($request->classroom_id);

        // 1 = Elementary / JHS
        // 3 = SHS
        
        $subject_column = ($classroom->grade_level_id <= 10) ? 1 : 3;
        

        DB::beginTransaction();
        try {

            foreach ($sheet as $key => $row) {
                if ($key > 1) {

                    if ($row[0] == null) { break; }
                    $line = $key-1;
                    $error = array();
                    $teacher_id = null;
                    $subject_id = null;


                    $data = array(
                        "email" => $row[0],
                        "subject" => $row[$subject_column],
                    );

                    $teacher = Teacher::where('email', $data['email'])
                        ->where('school_id', $school_id)->get();

                    if($teacher->count() == 0){
                        $error[] = 'Error on row '.$line.' : Email not found.';
                    }else{
                        $teacher_id = $teacher[0]->id;
                    }


                    if($data['subject'] == null){
                        $error[] = 'Error on row '.$line.' : Subject cannot be null.';
                    }else{
                        $subject = Subject::where('title', $data['subject'])->get();
                        if($subject->count() == 0){
                            $error[] = 'Error on row '.$line.' : Invalid Subject.';
                        }else{
                            $is_subject_exist = TeacherClass::where('classroom_id', $request->classroom_id)
                                ->where('subject_id', $subject[0]->id)
                                ->get();
                            
                            if($is_subject_exist->count() > 0 ){
                                $error[] = 'Error on row '.$line.' : Duplicate Subject Entry.';
                            }else{
                                $subject_id = $subject[0]->id;
                            }
                        }
                    }

                    if(sizeOf($error) > 0){
                        $errors[] = $error;
                    }else{
                        $teacher_class = new TeacherClass;
                        $teacher_class->classroom_id = $request->classroom_id; 
                        $teacher_class->teacher_id = $teacher_id; 
                        $teacher_class->subject_id = $subject_id; 
                        $teacher_class->advisory = 0;
                        $teacher_class->save();
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
            return redirect('/classrooms/'.$request->classroom_id)->with('success', 'Subject Class Uploader has been uploaded successfully.');
        } else {
            return back()->withErrors($result);
        }

    }


    public function add_student(Request $request){

        $this->validate($request, [
            'student_id' => 'required',
            'class_id' => 'required',
            'status' => 'required',
        ]);


        $is_existing = StudentClass::where([
            "student_id" => $request->student_id,
            "class_id" => $request->class_id,
        ])->get();
        if($is_existing->count() == 0){

            $student_class = new StudentClass;
            $student_class->class_id = $request->class_id;
            $student_class->status = $request->status;
            $student_class->student_id = $request->student_id;
            $student_class->save();

            return back()->with('success', 'Student has been added successfully.');

        }else{

            return back()->withErrors('Student already exists in this class!');
        }

            
    }


    public function update_student_status(Request $request){

        $student_class = StudentClass::where([
            'student_id' => $request->student_id,
            'class_id' => $request->class_id
        ])->first();

        $student_class->status = $request->status;
        $student_class->save();

        return redirect('/teacher_classes/'.$request->class_id)->with('success', 'Student status has been updated successfully.');


    }



}
