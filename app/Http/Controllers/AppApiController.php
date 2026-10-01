<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\ClassAssessment;
use App\Models\Classroom;
use App\Models\CustomFunction;
use App\Models\ECDC;
use App\Models\ECDCCompetency;
use App\Models\ECDCDomain;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\StudentECDC;
use App\Models\StudentScore;
use App\Models\Summative;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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

        // The API group is stateless, so authentication is determined by the
        // guard on the request. Querying the sessions table directly ignored the
        // guard and rejected every caller that was legitimately signed in.
        $authenticated_user_id = (int) Auth::id();

        if ($authenticated_user_id === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated request. Please login first.',
                'data' => null,
            ], 401);
        }

        if ($authenticated_user_id !== $user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Submitted userId does not match the active session.',
                'data' => null,
            ], 403);
        }

        $user = User::find($user_id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'data' => null,
            ], 404);
        }

        $teacher = Teacher::where('user_id', $user_id)->first();

        if (! $teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Only teacher accounts can sync classrooms.',
                'data' => null,
            ], 403);
        }

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
                ->map(fn ($u) => $u->only(['lrn', 'sectionId', 'gradeLevelId', 'classroomId', 'first_name', 'middle_name', 'last_name']))
                ->toArray(),
        ]);
    }

    public function getEcdcDomains()
    {
        $domains = ECDCDomain::query()
            ->orderBy('id')
            ->get()
            ->map(function ($domain) {
                return [
                    'id' => $domain->id,
                    'domain' => $domain->domain,
                    'competencies' => $domain->competencies()
                        ->orderBy('id')
                        ->get()
                        ->map(fn ($competency) => [
                            'id' => $competency->id,
                            'domain_id' => $competency->domain_id,
                            'competency' => $competency->competency,
                        ])
                        ->toArray(),
                ];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'message' => 'ECDC domains retrieved successfully.',
            'data' => $domains,
        ]);
    }

    public function syncEcdcDomains(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'domains' => ['required', 'array', 'min:1'],
            'domains.*.domain' => ['required', 'string', 'min:1'],
            'domains.*.competencies' => ['required', 'array', 'min:1'],
            'domains.*.competencies.*.competency' => ['required', 'string', 'min:1'],
        ], [
            'domains.required' => 'The ECDC domains payload is required.',
            'domains.min' => 'At least one domain is required.',
            'domains.*.domain.required' => 'Each domain name is required.',
            'domains.*.competencies.required' => 'Each domain must contain at least one competency.',
            'domains.*.competencies.min' => 'Each domain must contain at least one competency.',
            'domains.*.competencies.*.competency.required' => 'Each competency name is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        DB::beginTransaction();

        try {
            $savedDomains = [];

            foreach ($request->input('domains', []) as $domainPayload) {
                $domainName = trim((string) ($domainPayload['domain'] ?? ''));

                $domain = ECDCDomain::query()
                    ->whereRaw('LOWER(domain) = ?', [mb_strtolower($domainName)])
                    ->first();

                if (! $domain) {
                    $domain = new ECDCDomain;
                    $domain->domain = $domainName;
                    $domain->save();
                }

                $competencyNames = [];

                foreach ($domainPayload['competencies'] ?? [] as $competencyPayload) {
                    $competencyName = trim((string) ($competencyPayload['competency'] ?? $competencyPayload['name'] ?? ''));

                    if ($competencyName === '') {
                        continue;
                    }

                    ECDCCompetency::query()
                        ->where('domain_id', $domain->id)
                        ->whereRaw('LOWER(competency) = ?', [mb_strtolower($competencyName)])
                        ->firstOrCreate([
                            'domain_id' => $domain->id,
                            'competency' => $competencyName,
                        ]);

                    $competencyNames[] = mb_strtolower($competencyName);
                }

                if (empty($competencyNames)) {
                    ECDCCompetency::query()->where('domain_id', $domain->id)->delete();
                } else {
                    ECDCCompetency::query()
                        ->where('domain_id', $domain->id)
                        ->whereNotIn(DB::raw('LOWER(competency)'), $competencyNames)
                        ->delete();
                }

                $savedDomains[] = [
                    'id' => $domain->id,
                    'domain' => $domain->domain,
                    'competencies' => $domain->competencies()
                        ->orderBy('id')
                        ->get()
                        ->map(fn ($competency) => [
                            'id' => $competency->id,
                            'domain_id' => $competency->domain_id,
                            'competency' => $competency->competency,
                        ])
                        ->toArray(),
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ECDC domains synced successfully',
                'data' => $savedDomains,
            ]);
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('ECDC domain sync failed', [
                'exception' => $exception->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'ECDC domain sync failed.',
                'errors' => [
                    'general' => [$exception->getMessage()],
                ],
                'data' => null,
            ], 500);
        }
    }

    public function uploadAssessment(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'assessment_id' => ['required', 'integer'], // input field from app, input type NUMBER
                'class_id' => ['required', 'integer'], // from
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
        Log::info('Incoming assessment CSV:', [
            'assessment_id' => $request->assessment_id,
            'class_id' => $request->class_id,
            'raw_csv' => file_get_contents($csv_file_path),
        ]);
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

        Log::info('Assessment data:', $data);
        Log::info('Server-side answer key used to score this upload:', $assessment_keys);

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

    public function syncAssessment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:tbl_users,id',
        ], [
            'user_id.required' => 'User ID is required.',
            'user_id.integer' => 'User ID must be an integer.',
            'user_id.exists' => 'User ID does not exist.',
        ]);

        $user_id = $request->input('user_id');
        $teacher_id = Teacher::where('user_id', $user_id)->value('id');
        $assessments = CustomFunction::getAssessmentsDetailsByTeacherId($teacher_id)
            ->load(['assessmentKeys.questions.options']);

        return response()->json([
            'success' => true,
            'message' => 'Assessments retrieved successfully.',
            'data' => [
                'assessments' => $assessments->map(function ($assessment) {
                    return [
                        'id' => $assessment->id,
                        'title' => $assessment->assessment,
                        'number_of_items' => $assessment->number_of_items,
                        'from' => $assessment->from,
                        'to' => $assessment->to,
                        'level' => $assessment->level,
                        'subject' => $assessment->subject,
                        'type' => $assessment->type,
                        'period' => $assessment->period,
                        'assessment_keys' => $assessment->assessmentKeys->map(function ($key) {
                            return [
                                'question' => $key->questions->question,
                                'options' => $key->questions->options->map(function ($option) {
                                    return [
                                        'option' => $option->option,
                                        'is_correct' => (bool) $option->is_correct,
                                    ];
                                }),
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Stores the ECDC results captured by the mobile app.
     *
     * The app answers every competency of the synchronised domain list with
     * '1' (present), '-' (not present) or '*' (not tested). Present answers
     * become a score of 1 and not present answers a score of 0, exactly like
     * the web encoder. Not tested answers are deliberately left unrecorded:
     * the instrument has no mark for them, so they neither raise a domain
     * score nor print as an answer on the student card or the SF5-K report.
     *
     * Re-uploading a student replaces that student's answers for the period,
     * which keeps an interrupted sync retryable. Answers encoded by hand in
     * the web app are never replaced: that period belongs to the encoder, so
     * the request is refused instead of losing work silently.
     */
    public function uploadEcdcResults(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classroom_id' => ['required', 'integer', 'exists:tbl_classrooms,id'],
            'user_id' => ['required', 'integer', 'exists:tbl_users,id'],
            'period' => ['required', function (string $attribute, mixed $value, Closure $fail) {
                if (ECDC::normalizePeriod($value) === null) {
                    $fail('The period must be BOSY, MOSY, EOSY, 1, 2, or 3.');
                }
            }],
            'students' => ['required', 'array', 'min:1'],
            'students.*.lrn' => ['required', 'string', 'max:50'],
            'students.*.last_ticked_at' => ['nullable', 'date'],
            'students.*.responses' => ['required', 'array', 'min:1'],
            'students.*.responses.*.competency_id' => ['required', 'integer', 'exists:tbl_ecdc_competencies,id'],
            'students.*.responses.*.status' => ['required', Rule::in(['1', '-', '*'])],
        ], [
            'classroom_id.exists' => 'The classroom does not exist.',
            'user_id.exists' => 'The user does not exist.',
            'students.required' => 'At least one student result is required.',
            'students.min' => 'At least one student result is required.',
            'students.*.lrn.string' => 'Each LRN must be submitted as text.',
            'students.*.responses.required' => 'Each student must submit at least one competency response.',
            'students.*.responses.min' => 'Each student must submit at least one competency response.',
            'students.*.responses.*.competency_id.exists' => 'Each response must reference an existing ECDC competency.',
            'students.*.responses.*.status.in' => 'The status must be 1 (present), - (not present), or * (not tested).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        $user_id = (int) $request->input('user_id');
        $authenticated_user_id = (int) Auth::id();

        if ($authenticated_user_id === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated request. Please login first.',
                'data' => null,
            ], 401);
        }

        if ($authenticated_user_id !== $user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Submitted user_id does not match the active session.',
                'data' => null,
            ], 403);
        }

        $teacher = Teacher::where('user_id', $user_id)->first();

        if (! $teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Only teacher accounts can upload ECDC results.',
                'data' => null,
            ], 403);
        }

        $classroom = Classroom::find($request->input('classroom_id'));

        if ((int) $classroom->school_id !== (int) $teacher->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'The selected classroom does not belong to your school.',
                'data' => null,
            ], 403);
        }

        $academic_year = AcademicYear::active();

        if (! $academic_year) {
            return response()->json([
                'success' => false,
                'message' => 'No active academic year was found.',
                'errors' => [
                    'general' => ['No active academic year was found.'],
                ],
                'data' => null,
            ], 422);
        }

        $period = ECDC::normalizePeriod($request->input('period'));
        $students_payload = $request->input('students', []);

        // The distinct rule cannot be used here because two learners are
        // expected to answer the same competency, so duplicates are only
        // detected within a single learner's answer sheet.
        $duplicate_errors = [];

        foreach ($students_payload as $index => $student_payload) {
            $answered = [];

            foreach ($student_payload['responses'] as $response_index => $response) {
                $competency_id = (int) $response['competency_id'];

                if (isset($answered[$competency_id])) {
                    $duplicate_errors["students.$index.responses.$response_index.competency_id"] = ['A competency can only be answered once per student.'];
                }

                $answered[$competency_id] = true;
            }
        }

        if ($duplicate_errors !== []) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $duplicate_errors,
                'data' => null,
            ], 422);
        }

        $lrns = [];

        foreach ($students_payload as $student_payload) {
            $lrns[] = preg_replace('/\D/', '', (string) ($student_payload['lrn'] ?? ''));
        }

        $classroom_students = Student::query()
            ->select('tbl_students.id', 'tbl_students.lrn')
            ->join('tbl_student_classrooms', 'tbl_student_classrooms.student_id', 'tbl_students.id')
            ->where('tbl_student_classrooms.classroom_id', $classroom->id)
            ->whereIn('tbl_students.lrn', array_unique($lrns))
            ->get()
            ->keyBy('lrn');

        $errors = [];
        $submitted_lrns = [];

        foreach ($lrns as $index => $lrn) {
            if ($lrn === '') {
                $errors["students.$index.lrn"] = ['The LRN must contain digits.'];
            } elseif (isset($submitted_lrns[$lrn])) {
                $errors["students.$index.lrn"] = ["LRN $lrn is submitted more than once."];
            } elseif (! $classroom_students->has($lrn)) {
                $errors["students.$index.lrn"] = ["LRN $lrn does not exist on this classroom."];
            } else {
                $submitted_lrns[$lrn] = true;
            }
        }

        if ($errors !== []) {
            return response()->json([
                'success' => false,
                'message' => 'One or more students cannot be uploaded to this classroom.',
                'errors' => $errors,
                'data' => null,
            ], 422);
        }

        $latest_date = null;

        foreach ($students_payload as $student_payload) {
            $ticked_at = $student_payload['last_ticked_at'] ?? null;

            if (filled($ticked_at)) {
                $ticked_date = Carbon::parse($ticked_at)->toDateString();

                if ($latest_date === null || $ticked_date > $latest_date) {
                    $latest_date = $ticked_date;
                }
            }
        }

        DB::beginTransaction();

        try {
            $now = Carbon::now('Asia/Manila')->toDateTimeString();

            $ecdc = ECDC::query()
                ->where('classroom_id', $classroom->id)
                ->where('academic_year_id', $academic_year->id)
                ->where('period', $period)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            // Answers encoded by hand belong to the web encoder, so a mobile
            // sync must never replace them silently.
            if ($ecdc && (int) $ecdc->source !== 1) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'This period already has results encoded in the web app, which have to be edited there.',
                    'errors' => [
                        'period' => ['Period '.$period.' already has encoded results for this classroom.'],
                    ],
                    'data' => null,
                ], 422);
            }

            if (! $ecdc) {
                $ecdc = new ECDC;
                $ecdc->classroom_id = $classroom->id;
                $ecdc->academic_year_id = $academic_year->id;
                $ecdc->period = $period;
                $ecdc->teacher_id = $teacher->id;
                $ecdc->source = '1';
                $ecdc->date = $latest_date ?? Carbon::now('Asia/Manila')->toDateString();
                $ecdc->created_at = $now;
                $ecdc->save();
            } elseif ($latest_date !== null && $latest_date > (string) $ecdc->date) {
                $ecdc->date = $latest_date;
                $ecdc->save();
            }

            $students_uploaded = [];
            $responses_saved = 0;
            $responses_skipped = 0;

            foreach ($students_payload as $index => $student_payload) {
                $student_id = (int) $classroom_students[$lrns[$index]]->id;
                $rows = [];
                $saved = 0;
                $skipped = 0;

                foreach ($student_payload['responses'] as $response) {
                    $status = (string) $response['status'];

                    if ($status === '*') {
                        $skipped++;

                        continue;
                    }

                    $rows[] = [
                        'student_id' => $student_id,
                        'ecdc_id' => $ecdc->id,
                        'ecdc_competency_id' => (int) $response['competency_id'],
                        'score' => $status === '1' ? 1 : 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $saved++;
                }

                StudentECDC::query()
                    ->where('ecdc_id', $ecdc->id)
                    ->where('student_id', $student_id)
                    ->delete();

                if ($rows !== []) {
                    StudentECDC::query()->insert($rows);
                }

                $responses_saved += $saved;
                $responses_skipped += $skipped;

                $students_uploaded[] = [
                    'lrn' => $lrns[$index],
                    'student_id' => $student_id,
                    'responses_saved' => $saved,
                    'responses_skipped' => $skipped,
                ];
            }

            $ecdc_id = (int) $ecdc->id;
            $date_tested = $ecdc->date;

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('ECDC result upload failed', [
                'exception' => $exception->getMessage(),
                'classroom_id' => $classroom->id,
                'user_id' => $user_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'ECDC result upload failed.',
                'errors' => [
                    'general' => [$exception->getMessage()],
                ],
                'data' => null,
            ], 500);
        }

        // The student cards, the printed forms, and the SF5-K report read the
        // scored result from a cached file, so it is rebuilt here exactly like
        // the web encoder does. A failure stays recoverable because the report
        // regenerates the file whenever it is missing.
        try {
            ECDC::getResults($ecdc_id);
        } catch (Exception $exception) {
            Log::error('ECDC result cache could not be regenerated after a mobile upload', [
                'ecdc_id' => $ecdc_id,
                'exception' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'ECDC results uploaded successfully.',
            'errors' => null,
            'data' => [
                'ecdc_id' => $ecdc_id,
                'classroom_id' => (int) $classroom->id,
                'academic_year_id' => (int) $academic_year->id,
                'period' => $period,
                'date' => $date_tested,
                'students_uploaded' => count($students_uploaded),
                'responses_saved' => $responses_saved,
                'responses_skipped' => $responses_skipped,
                'students' => $students_uploaded,
            ],
        ]);
    }

    /**
     * Reports which ECDC periods already have results on the server, per
     * learner, so the mobile app can mark a sheet as uploaded and offer the
     * teacher a deliberate re-upload instead of duplicating work. Both encoded
     * and uploaded events are reported; the app must not overwrite an encoded
     * sheet, because the upload endpoint refuses it.
     */
    public function syncEcdcResults(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classroom_id' => ['required', 'integer', 'exists:tbl_classrooms,id'],
            'user_id' => ['required', 'integer', 'exists:tbl_users,id'],
        ], [
            'classroom_id.exists' => 'The classroom does not exist.',
            'user_id.exists' => 'The user does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        $user_id = (int) $request->input('user_id');
        $authenticated_user_id = (int) Auth::id();

        if ($authenticated_user_id === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated request. Please login first.',
                'data' => null,
            ], 401);
        }

        if ($authenticated_user_id !== $user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Submitted user_id does not match the active session.',
                'data' => null,
            ], 403);
        }

        $teacher = Teacher::where('user_id', $user_id)->first();

        if (! $teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Only teacher accounts can sync ECDC results.',
                'data' => null,
            ], 403);
        }

        $classroom = Classroom::find($request->input('classroom_id'));

        if ((int) $classroom->school_id !== (int) $teacher->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'The selected classroom does not belong to your school.',
                'data' => null,
            ], 403);
        }

        $academic_year = AcademicYear::active();

        if (! $academic_year) {
            return response()->json([
                'success' => false,
                'message' => 'No active academic year was found.',
                'errors' => [
                    'general' => ['No active academic year was found.'],
                ],
                'data' => null,
            ], 422);
        }

        $events = ECDC::query()
            ->where('classroom_id', $classroom->id)
            ->where('academic_year_id', $academic_year->id)
            ->orderBy('period')
            ->orderBy('id')
            ->get();

        $answers = $events->isEmpty() ? collect() : StudentECDC::query()
            ->selectRaw('student_id, ecdc_id, COUNT(*) as responses_saved, MAX(updated_at) as last_answered_at')
            ->whereIn('ecdc_id', $events->pluck('id')->all())
            ->groupBy('student_id')
            ->groupBy('ecdc_id')
            ->get()
            ->keyBy(fn ($answer) => $answer->student_id.'-'.$answer->ecdc_id);

        $learners = Student::query()
            ->select(
                'tbl_students.id',
                'tbl_students.lrn',
                'tbl_persons.first_name',
                'tbl_persons.middle_name',
                'tbl_persons.last_name'
            )
            ->join('tbl_student_classrooms', 'tbl_student_classrooms.student_id', 'tbl_students.id')
            ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('tbl_student_classrooms.classroom_id', $classroom->id)
            ->orderBy('tbl_persons.last_name')
            ->orderBy('tbl_persons.first_name')
            ->get();

        $students = [];

        foreach ($learners as $learner) {
            $recorded_periods = [];

            foreach ($events as $event) {
                $answer = $answers->get($learner->id.'-'.$event->id);

                if (! $answer) {
                    continue;
                }

                $recorded_periods[] = [
                    'ecdc_id' => (int) $event->id,
                    'period' => (string) $event->period,
                    'label' => ECDC::periodLabel($event->period),
                    'source' => (int) $event->source,
                    'responses_saved' => (int) $answer->responses_saved,
                    'last_answered_at' => $answer->last_answered_at,
                ];
            }

            $students[] = [
                'student_id' => (int) $learner->id,
                'lrn' => $learner->lrn,
                'name' => $learner->last_name.', '.$learner->first_name.' '.$learner->middle_name,
                'periods' => $recorded_periods,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'ECDC results synced successfully.',
            'errors' => null,
            'data' => [
                'classroom_id' => (int) $classroom->id,
                'academic_year_id' => (int) $academic_year->id,
                'competencies_per_sheet' => ECDCCompetency::query()->count(),
                'periods' => $events->map(fn ($event) => [
                    'ecdc_id' => (int) $event->id,
                    'period' => (string) $event->period,
                    'label' => ECDC::periodLabel($event->period),
                    'date' => $event->date,
                    'source' => (int) $event->source,
                    'students_recorded' => $answers->where('ecdc_id', $event->id)->count(),
                    'responses_saved' => (int) $answers->where('ecdc_id', $event->id)->sum('responses_saved'),
                ])->values()->all(),
                'students' => $students,
            ],
        ]);
    }
}
