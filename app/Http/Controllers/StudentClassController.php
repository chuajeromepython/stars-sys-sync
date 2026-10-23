<?php

namespace App\Http\Controllers;

use App\Models\StudentClass;
use App\Models\StudentClassroom;
use App\Models\TeacherClass;
use App\Support\UserHierarchy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StudentClassController extends Controller
{
    public function __construct(private UserHierarchy $userHierarchy) {}

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(StudentClass $studentClass)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(StudentClass $studentClass)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, StudentClass $studentClass)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(StudentClass $studentClass)
    {
        //
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => ['required', 'integer', 'exists:tbl_classrooms,id'],
            'class_id' => ['required', 'integer', 'exists:tbl_teacher_classes,id'],
        ]);

        $teacherClass = TeacherClass::query()
            ->with('classroom:id,school_id')
            ->whereKey($validated['class_id'])
            ->where('classroom_id', $validated['classroom_id'])
            ->firstOrFail();

        $hierarchy = $this->userHierarchy->forUser($request->user());
        $isAllowed = match ($hierarchy['scope']) {
            'system' => true,
            'teacher' => (int) $teacherClass->teacher_id === $hierarchy['teacher_id'],
            'school' => (int) $teacherClass->classroom?->school_id === $hierarchy['school_id'],
            default => false,
        };

        abort_unless($isAllowed, 404);

        $students = StudentClassroom::query()
            ->where('classroom_id', $validated['classroom_id'])
            ->get();
        $studentCount = 0;

        DB::transaction(function () use ($students, $teacherClass, &$studentCount): void {
            foreach ($students as $key => $student) {
                $existing = StudentClass::where([
                    'student_id' => $student->student_id,
                    'class_id' => $teacherClass->id,
                ])->exists();

                if (! $existing) {
                    $student_class = new StudentClass;
                    $student_class->student_id = $student->student_id;
                    $student_class->class_id = $teacherClass->id;
                    $student_class->status = $student->status;
                    $student_class->save();
                    $studentCount++;
                }
            }
        });

        return back()->with('success', $studentCount.'/'.$students->count().' Students have been synced successfully.');
    }
}
