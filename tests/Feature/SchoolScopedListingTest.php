<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CustomFunction;
use App\Models\DepartmentHead;
use App\Models\GradeLevel;
use App\Models\Person;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * The school scoped listings used to resolve the school from tbl_school_supervisors
 * only. A teacher or a department head has no row there, so the lookup produced
 * a null school, the query filtered on school_id = null and every table came
 * back empty. These cover the resolution and the scoping together.
 */
class SchoolScopedListingTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    private ?School $school = null;

    private ?School $otherSchool = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    /**
     * The classroom listing called a private method as a bare function, which
     * raised "Undefined variable $canManageClassrooms" for every role.
     */
    public function test_the_classroom_listing_renders_for_a_school_head(): void
    {
        $this->activeAcademicYear();

        $this->actingAs($this->schoolHead($this->school()))
            ->get('/classrooms')
            ->assertOk();
    }

    public function test_the_classroom_listing_renders_for_a_teacher(): void
    {
        $this->activeAcademicYear();

        $this->actingAs($this->teacher($this->school()))
            ->get('/classrooms')
            ->assertOk();
    }

    public function test_a_school_head_sees_every_classroom_in_their_school(): void
    {
        $this->activeAcademicYear();

        $this->classroomIn($this->school(), 'A');
        $this->classroomIn($this->school(), 'B');
        $this->classroomIn($this->otherSchool(), 'Z');

        $response = $this->actingAs($this->schoolHead($this->school()))->get('/classrooms');

        $response->assertOk();
        $response->assertDontSee('>Z<', false);
    }

    public function test_a_teacher_sees_only_the_classrooms_they_are_assigned_to(): void
    {
        $this->activeAcademicYear();

        $teacher = $this->teacher($this->school());
        $teacherId = Teacher::where('user_id', $teacher->id)->value('id');

        $assigned = $this->classroomIn($this->school(), 'A');
        $this->classroomIn($this->school(), 'B');

        DB::table('tbl_teacher_classes')->insert([
            'classroom_id' => $assigned->id,
            'teacher_id' => $teacherId,
            'subject_id' => DB::table('tbl_subjects')->insertGetId(['title' => 'Mathematics']),
            'advisory' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($teacher)->get('/classrooms');

        $response->assertOk();
        $response->assertDontSee('>B<', false);
    }

    /**
     * The student listing resolved a null school for a teacher, so the table
     * rendered with no rows at all despite the school having students.
     */
    public function test_a_teacher_sees_the_students_of_their_school(): void
    {
        $this->studentIn($this->school(), 'lrn-0001');

        $response = $this->actingAs($this->teacher($this->school()))
            ->getJson('/students/data?draw=1&start=0&length=10');

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsTotal'));
    }

    public function test_a_school_head_sees_only_the_students_of_their_school(): void
    {
        $this->studentIn($this->school(), 'lrn-own');
        $this->studentIn($this->otherSchool(), 'lrn-other');

        $response = $this->actingAs($this->schoolHead($this->school()))
            ->getJson('/students/data?draw=1&start=0&length=10');

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertStringNotContainsString('lrn-other', $response->getContent());
    }

    /**
     * Only a role with the teachers permission can open the listing at all, so
     * this is asserted through a school head; the point is the school scoping.
     */
    public function test_a_school_head_sees_only_the_teachers_of_their_school(): void
    {
        $this->teacher($this->school(), 'colleague');
        $this->teacher($this->otherSchool(), 'outsider');

        $response = $this->actingAs($this->schoolHead($this->school()))
            ->getJson('/teachers/data?draw=1&start=0&length=10');

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertStringNotContainsString('outsider', $response->getContent());
    }

    /**
     * The department head listing also resolved a null school, so the table was
     * empty even when the school had department heads.
     */
    public function test_a_department_head_listing_is_scoped_to_the_school(): void
    {
        $this->departmentHead($this->school(), 'own');
        $this->departmentHead($this->otherSchool(), 'other');

        $response = $this->actingAs($this->departmentHead($this->school(), 'viewer'))->get('/department_heads');

        $response->assertOk();
        $response->assertSee('own');
        $response->assertDontSee('other');
    }

    /**
     * A role with no school record at all must see nothing rather than
     * everything, so a missing link can never widen a listing.
     */
    public function test_a_role_without_a_school_record_sees_no_rows(): void
    {
        $this->studentIn($this->school(), 'lrn-0001');

        $unaffiliated = $this->makeUser(Role::DivisionAdministrator->value, 'division@deped.gov.ph');

        $response = $this->actingAs($unaffiliated)
            ->getJson('/students/data?draw=1&start=0&length=10');

        $response->assertOk();
        $this->assertSame(0, $response->json('recordsTotal'));
    }

    public function test_a_department_head_resolves_their_school(): void
    {
        $departmentHead = $this->departmentHead($this->school(), 'resolve');

        $this->assertSame(
            $this->school()->id,
            CustomFunction::resolveSchoolIdForUser($departmentHead)
        );

        $this->assertInstanceOf(
            DepartmentHead::class,
            DepartmentHead::where('user_id', $departmentHead->id)->first()
        );
    }

    private function school(): School
    {
        if ($this->school === null) {
            $this->school = $this->makeSchool('SCH-ONE', 'Main School');
        }

        return $this->school;
    }

    private function otherSchool(): School
    {
        if ($this->otherSchool === null) {
            $this->otherSchool = $this->makeSchool('SCH-TWO', 'Annex School');
        }

        return $this->otherSchool;
    }

    private function makeSchool(string $code, string $name): School
    {
        if (! DB::table('tbl_school_types')->where('type', 'Elementary')->exists()) {
            DB::table('tbl_school_types')->insert(['type' => 'Elementary']);
        }

        $school = new School;
        $school->code = $code;
        $school->name = $name;
        $school->address = 'Somewhere';
        $school->school_category_id = 1;
        $school->school_type_id = DB::table('tbl_school_types')->where('type', 'Elementary')->value('id');
        $school->district_id = 1;
        $school->save();

        return $school;
    }

    private function activeAcademicYear(): AcademicYear
    {
        $existing = AcademicYear::where('is_active', 1)->first();

        if ($existing !== null) {
            return $existing;
        }

        $academicYear = new AcademicYear;
        $academicYear->from = '2026';
        $academicYear->to = '2027';
        $academicYear->is_active = 1;
        $academicYear->save();

        return $academicYear;
    }

    private function classroomIn(School $school, string $section): Classroom
    {
        $sectionModel = Section::where('section', $section)
            ->where('school_id', $school->id)
            ->first();

        if ($sectionModel === null) {
            $sectionModel = new Section;
            $sectionModel->section = $section;
            $sectionModel->school_id = $school->id;
            $sectionModel->save();
        }

        $gradeLevel = GradeLevel::where('level', 'Grade 1')->first();

        if ($gradeLevel === null) {
            $gradeLevel = new GradeLevel;
            $gradeLevel->level = 'Grade 1';
            $gradeLevel->save();
        }

        $classroom = new Classroom;
        $classroom->section_id = $sectionModel->id;
        $classroom->grade_level_id = $gradeLevel->id;
        $classroom->academic_year_id = $this->activeAcademicYear()->id;
        $classroom->school_id = $school->id;
        $classroom->save();

        return $classroom;
    }

    private function schoolHead(School $school): User
    {
        $user = $this->makeUser('School Head', $school->code.'-head@deped.gov.ph');

        DB::table('tbl_school_supervisors')->insert([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'status' => 1,
            'email' => $user->username,
        ]);

        return $user;
    }

    private function teacher(School $school, string $sequence = 'one'): User
    {
        $user = $this->makeUser('Teacher', $school->code.'-teacher-'.$sequence.'@deped.gov.ph');

        DB::table('tbl_teachers')->insert([
            'email' => $user->username,
            'user_id' => $user->id,
            'school_id' => $school->id,
        ]);

        return $user;
    }

    private function departmentHead(School $school, string $sequence): User
    {
        $user = $this->makeUser('Department Head', $school->code.'-dept-'.$sequence.'@deped.gov.ph');

        DB::table('tbl_department_heads')->insert([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'subject_id' => '[]',
            'email' => $user->username,
        ]);

        return $user;
    }

    private function studentIn(School $school, string $lrn): Student
    {
        $user = $this->makeUser('Student', $school->code.'-'.$lrn.'@deped.gov.ph');

        $student = new Student;
        $student->lrn = $lrn;
        $student->email = $user->username;
        $student->user_id = $user->id;
        $student->school_id = $school->id;
        $student->save();

        return $student;
    }

    private function makeUser(string $classification, string $username): User
    {
        $person = new Person;
        $person->first_name = 'Test';
        $person->middle_name = '';
        $person->last_name = str($username)->before('@')->replaceLast('.', ' ')->toString();
        $person->suffix = '';
        $person->gender = 'M';
        $person->birth_date = '1990-01-01';
        $person->save();

        $user = new User;
        $user->username = $username;
        $user->password = Hash::make('password123');
        $user->classification = $classification;
        $user->status = true;
        $user->person_id = $person->id;
        $user->save();

        $this->assertTrue(
            $user->fresh()->hasRole($classification),
            sprintf('A [%s] account must resolve to its role.', $classification)
        );

        return $user;
    }
}
