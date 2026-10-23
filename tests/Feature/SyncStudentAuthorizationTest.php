<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\District;
use App\Models\Division;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class SyncStudentAuthorizationTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    public function test_teacher_can_sync_students_into_their_own_subject_class(): void
    {
        [$teacherUser, $teacherClassId, $classroom, $student] = $this->subjectClass();

        $this->actingAs($teacherUser)->post('/student_classes/sync', [
            'classroom_id' => $classroom->id,
            'class_id' => $teacherClassId,
        ])->assertRedirect();

        $this->assertDatabaseHas('tbl_student_classes', [
            'student_id' => $student->id,
            'class_id' => $teacherClassId,
            'status' => 1,
        ]);
    }

    public function test_teacher_cannot_sync_students_into_another_teachers_class(): void
    {
        [$teacherUser, , $classroom] = $this->subjectClass();
        $otherTeacherUser = $this->userWithRole(Role::Teacher);
        $otherTeacher = $this->createTeacher($otherTeacherUser, $classroom->school_id);
        $otherClassId = $this->assignTeacher($otherTeacher, $classroom);

        $this->actingAs($teacherUser)->post('/student_classes/sync', [
            'classroom_id' => $classroom->id,
            'class_id' => $otherClassId,
        ])->assertNotFound();

        $this->assertDatabaseCount('tbl_student_classes', 0);
    }

    public function test_sync_students_requires_the_teacher_class_management_permission(): void
    {
        [, $teacherClassId, $classroom] = $this->subjectClass();
        $user = $this->userWithRole(Role::DivisionSupervisor);

        $this->actingAs($user)->post('/student_classes/sync', [
            'classroom_id' => $classroom->id,
            'class_id' => $teacherClassId,
        ])->assertRedirect('/forbidden');

        $this->assertDatabaseCount('tbl_student_classes', 0);
    }

    /**
     * @return array{User, int, Classroom, Student}
     */
    private function subjectClass(): array
    {
        $division = new Division;
        $division->name = 'Division';
        $division->save();
        $district = new District;
        $district->name = 'District';
        $district->division_id = $division->id;
        $district->save();
        $school = new School;
        $school->code = 'SCHOOL-1';
        $school->name = 'School';
        $school->address = 'Address';
        $school->school_category_id = 1;
        $school->school_type_id = 1;
        $school->district_id = $district->id;
        $school->save();
        $year = new AcademicYear;
        $year->from = '2026';
        $year->to = '2027';
        $year->is_active = 1;
        $year->save();
        $classroom = new Classroom;
        $classroom->section_id = 1;
        $classroom->grade_level_id = 1;
        $classroom->academic_year_id = $year->id;
        $classroom->school_id = $school->id;
        $classroom->save();

        $teacherUser = $this->userWithRole(Role::Teacher);
        $teacher = $this->createTeacher($teacherUser, $school->id);
        $teacherClassId = $this->assignTeacher($teacher, $classroom);
        $studentUser = $this->userWithRole(Role::Student);
        $student = new Student;
        $student->user_id = $studentUser->id;
        $student->school_id = $school->id;
        $student->lrn = 'SYNC-001';
        $student->email = 'sync-001@student.test';
        $student->save();
        DB::table('tbl_student_classrooms')->insert([
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'status' => 1,
            'is_uploaded' => 1,
        ]);

        return [$teacherUser, $teacherClassId, $classroom, $student];
    }

    private function createTeacher(User $user, int $schoolId): Teacher
    {
        $teacher = new Teacher;
        $teacher->user_id = $user->id;
        $teacher->school_id = $schoolId;
        $teacher->email = $user->username;
        $teacher->save();

        return $teacher;
    }

    private function assignTeacher(Teacher $teacher, Classroom $classroom): int
    {
        return DB::table('tbl_teacher_classes')->insertGetId([
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'subject_id' => DB::table('tbl_subjects')->insertGetId(['title' => uniqid('Subject-')]),
            'advisory' => 0,
        ]);
    }
}
