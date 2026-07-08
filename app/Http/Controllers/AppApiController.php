<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\ClassAssessment;
use App\Models\CustomFunction;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\StudentScore;
use App\Models\Summative;
use App\Models\Teacher;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AppApiController extends Controller
{
    public function authenticate($username, $password)
    {

        $credentials = [
            'username' => $username,
            'password' => $password,
        ];
        $result = [
            'status' => 0,
            'data' => null,
            'message' => 'Not Found',
        ];
        if (Auth::attempt($credentials)) {
            $result['data'] = Auth::user();
            $result['status'] = '200';
            $result['message'] = 'Successfully login';
        }

        return response()->json($result);
    }

    public function syncClassroomsByTeacherUserId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => ['required', 'integer', 'exists:tbl_users,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        $user_id = (int) $request->input('userId');

        $isAuthenticated = DB::table('sessions')
            ->where('user_id', $user_id)
            ->where('last_activity', '>=', now()->subMinutes(15)->timestamp) // your "active" threshold
            ->exists();

        if (! $isAuthenticated) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated request. Please login first.',
                'data' => null,
            ], 401);
        }

        // if ((int) Auth::id() !== $user_id) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Submitted userId does not match the active session.',
        //         'data' => null,
        //     ], 403);
        // }

        $user = User::find($user_id);

        if (! $user || $user->classification !== 'Teacher') {
            return response()->json([
                'success' => false,
                'message' => 'Only teacher accounts can sync classrooms.',
                'data' => null,
            ], 403);
        }

        $teacher = Teacher::where('user_id', $user_id)->first();

        if (! $teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found.',
                'data' => null,
            ], 404);
        }

        Auth::loginUsingId($user_id);

        $classrooms = CustomFunction::getClassroomsByTeacherUserId($user_id);

        if (empty($classrooms)) {
            return response()->json([
                'success' => true,
                'message' => 'No classrooms found',
                'data' => (object) [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Classrooms synced successfully',
            'data' => $classrooms,
        ]);
    }

    public function studentsPerClassroom(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|integer|exists:tbl_classrooms,id',
        ], [
            'classroom_id.required' => 'Classroom ID is required.',
            'classroom_id.integer' => 'Classroom ID must be an integer.',
            'classroom_id.exists' => 'Classroom ID does not exist.',
        ]);

        $classroom_id = $request->input('classroom_id');

        $students = CustomFunction::getStudentsPerClassroom($classroom_id);

        return response()->json([
            'success' => true,
            'message' => 'Students retrieved successfully.',
            'data' => $students
                ->map(fn ($u) => $u->only(['lrn', 'sectionId', 'gradeLevelId', 'classroomId']))
                ->toArray(),
        ]);
    }

    public function uploadAssessment(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'assessment_id' => ['required', 'integer'],
                'class_id' => ['required', 'integer'],
                'file_assessment' => ['required', 'mimes:csv,txt'],
            ],
            [
                'file_assessment.mimes' => 'The file must be a file of type: csv, txt.',
                'file_assessment.required' => 'Please upload a file.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        $assessment = Assessment::find($request->assessment_id);

        if (! $assessment) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment not found.',
                'errors' => [
                    'assessment_id' => ['The selected assessment is invalid.'],
                ],
                'data' => null,
            ], 404);
        }

        $assessment_keys = CustomFunction::getAssessmentKeys($request->assessment_id);
        $data = [];
        $error = [];
        $sheets = [];

        $file_assessment = $request->file('file_assessment');
        $csv_file_path = $file_assessment->getRealPath();
        $assessment_csv = fopen($csv_file_path, 'r');
        while (! feof($assessment_csv)) {
            $sheets[] = fgetcsv($assessment_csv, 0, ';');
        }
        fclose($assessment_csv);

        foreach ($sheets as $students) {
            if ($students) {
                $fortmattedLRN = '';
                $lrn = '';
                $score = 0;
                $answer = [];
                foreach ($students as $key => $value) {
                    if ($key < 12) {
                        $lrn .= $value;
                    } else {
                        $item_number = $key - 11;
                        if (array_key_exists($item_number, $assessment_keys)) {
                            $is_correct = ($assessment_keys[$item_number] == $value) ? true : false;
                            $score = ($assessment_keys[$item_number] == $value) ? $score + 1 : $score;
                            $answer[$item_number] = [
                                'answer' => $value,
                                'is_correct' => $is_correct,
                            ];
                        }
                    }
                }

                $fortmattedLRN = preg_replace('/\D/', '', $lrn);

                $student = Student::select('student_id')
                    ->join('tbl_student_classes', 'tbl_student_classes.student_id', 'tbl_students.id')
                    ->where('lrn', $fortmattedLRN)
                    ->where('class_id', $request->class_id)
                    ->get();

                if ($student->count() == 0 || empty($fortmattedLRN)) {
                    $error[] = $fortmattedLRN.' does not exist on this class.';
                } else {
                    $data[$student[0]->student_id] = [
                        'score' => $score,
                        'answer' => $answer,
                    ];
                }
            }
        }

        if (count($error) == 0) {

            DB::beginTransaction();
            try {

                $is_class_assessment_existing = ClassAssessment::where('class_id', $request->class_id)
                    ->where('assessment_id', $request->assessment_id)
                    ->get();

                if ($is_class_assessment_existing->count() == 0) {
                    $class_assessment = new ClassAssessment;
                    $class_assessment->class_id = $request->class_id;
                    $class_assessment->assessment_id = $request->assessment_id;
                    $class_assessment->save();
                } else {
                    $class_assessment = $is_class_assessment_existing[0];
                }

                foreach ($data as $student_id => $answers) {

                    $is_student_existing = StudentScore::where([
                        'class_assessment_id' => $request->assessment_id,
                        'student_id' => $student_id,
                    ])->get();

                    if ($is_student_existing->count() == 0) {

                        StudentScore::firstOrCreate(
                            [
                                'student_id' => $student_id,
                                'class_assessment_id' => $class_assessment->id,
                            ],
                            [
                                'score' => $answers['score'],
                                'student_id' => $student_id,
                                'class_assessment_id' => $class_assessment->id,
                            ]
                        );

                        // $student_score = StudentScore::where('class_assessment_id', $class_assessment->id)
                        //     ->where('student_id', $student_id)
                        //     ->first();

                        // if(empty($student_score)){
                        //     dd($student_id, $answers);
                        // }
                        // $student_score->student_id = $student_id;
                        // $student_score->score = $answers['score'];
                        // $student_score->class_assessment_id = $class_assessment->id;
                        // $student_score->save();

                        foreach ($answers['answer'] as $item_number => $answer) {
                            $student_answer = StudentAnswer::where('student_id', $student_id)
                                ->where('item_number', $item_number)
                                ->where('class_assessment_id', $class_assessment->id)
                                ->first();

                            if (empty($student_answer)) {
                                $student_answer = new StudentAnswer;
                            }

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

            if ($result === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'Class Assessment uploaded successfully.',
                    'errors' => null,
                    'data' => [
                        'assessment_id' => (int) $request->assessment_id,
                        'class_id' => (int) $request->class_id,
                        'students_uploaded' => count($data),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload class assessment.',
                'errors' => [
                    'upload' => [$result],
                ],
                'data' => null,
            ], 500);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed. Some students were not found in this class.',
                'errors' => [
                    'students' => $error,
                ],
                'data' => null,
            ], 422);
        }
    }

    public function uploadSummativeAssessment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_answer_key' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file_answer_key.required' => 'Please upload a file.',
            'file_answer_key.mimes' => 'The file must be an Excel file (xlsx or xls).',
            'file_answer_key.max' => 'The file size must not exceed 10MB.',
        ]);

        if ($validator->fails()) {
            return redirect('/summatives')->withErrors($validator);
        }

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
