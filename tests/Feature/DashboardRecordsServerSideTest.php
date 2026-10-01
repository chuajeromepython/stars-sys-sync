<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\District;
use App\Models\Division;
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

class DashboardRecordsServerSideTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    public function test_records_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/dashboard/records?type=teachers');

        $response->assertRedirect('/login');
    }

    private ?School $school = null;

    private function school(): School
    {
        if ($this->school === null) {
            $this->school = new School;
            $this->school->code = 'SANTA-MARIA';
            $this->school->name = 'Santa Maria Integrated NS';
            $this->school->address = 'Santa Maria';
            $this->school->school_category_id = 1;
            $this->school->school_type_id = 1;
            $this->school->district_id = 1;
            $this->school->save();
        }

        return $this->school;
    }

    public function test_records_endpoint_returns_datatables_contract_and_echoes_draw(): void
    {
        $user = $this->createSchoolHead();

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=teachers&draw=7&start=0&length=10');

        $response->assertOk();
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);

        $this->assertSame(7, $response->json('draw'));
    }

    public function test_records_endpoint_paginates_teachers(): void
    {
        $user = $this->createSchoolHead();

        foreach (['Alpha', 'Bravo', 'Charlie'] as $sequence) {
            $this->createUser('Teacher', ['last_name' => $sequence, 'sequence' => $sequence]);
        }

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=teachers&draw=1&start=0&length=2');

        $response->assertOk();

        $this->assertSame(3, $response->json('recordsTotal'));
        $this->assertSame(3, $response->json('recordsFiltered'));
        $this->assertCount(2, $response->json('data'));
        $this->assertArrayHasKey('record', $response->json('data.0'));
        $this->assertArrayHasKey('detail', $response->json('data.0'));
        $this->assertArrayHasKey('action', $response->json('data.0'));
    }

    public function test_records_endpoint_filters_students_by_search_term(): void
    {
        $user = $this->createSchoolHead();
        $this->createStudent('lrn-0001', 'Matching');
        $this->createStudent('lrn-0002', 'Different');

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=students&draw=1&start=0&length=10&search[value]=Matching');

        $response->assertOk();

        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('lrn-0001', $response->json('data.0.detail'));
    }

    public function test_records_endpoint_searches_students_by_lrn(): void
    {
        $user = $this->createSchoolHead();
        $this->createStudent('lrn-0001', 'Zulu');
        $this->createStudent('lrn-9999', 'Alpha');

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=students&draw=1&start=0&length=10&search[value]=lrn-9999');

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame('lrn-9999', $response->json('data.0.detail'));
    }

    public function test_records_endpoint_ignores_non_searchable_action_column(): void
    {
        $user = $this->createSchoolHead();
        $this->createStudent('lrn-0001', 'Matching');

        $response = $this->actingAs($user)->getJson(
            '/dashboard/records?type=students&draw=1&start=0&length=10&search[value]=btn-primary'
        );

        $response->assertOk();

        $this->assertSame(0, $response->json('recordsFiltered'));
    }

    public function test_records_endpoint_orders_students_by_last_name(): void
    {
        $user = $this->createSchoolHead();
        $this->createStudent('lrn-0001', 'Zulu');
        $this->createStudent('lrn-0002', 'Alpha');

        $response = $this->actingAs($user)->getJson(
            '/dashboard/records?type=students&draw=1&start=0&length=10&order[0][column]=0&order[0][dir]=asc'
        );

        $response->assertOk();

        $this->assertStringContainsString('Alpha', $response->json('data.0.record'));
        $this->assertSame('lrn-0002', $response->json('data.0.detail'));
    }

    public function test_records_endpoint_ignores_unknown_order_column(): void
    {
        $user = $this->createSchoolHead();
        $this->createStudent('lrn-0001', 'Zulu');

        $response = $this->actingAs($user)->getJson(
            '/dashboard/records?type=students&draw=1&start=0&length=10&order[0][column]=99&order[0][dir]=asc'
        );

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_records_endpoint_handles_start_that_is_not_a_multiple_of_length(): void
    {
        $user = $this->createSchoolHead();

        foreach (['Alpha', 'Bravo', 'Charlie', 'Delta'] as $sequence) {
            $this->createUser('Teacher', ['last_name' => $sequence, 'sequence' => $sequence]);
        }

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=teachers&draw=1&start=3&length=2');

        $response->assertOk();

        // Four teachers total, so the second page of two returns the final two rows.
        $this->assertSame(4, $response->json('recordsTotal'));
        $this->assertCount(2, $response->json('data'));
    }

    public function test_records_endpoint_caps_show_all_requests(): void
    {
        $user = $this->createSchoolHead();

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=teachers&draw=1&start=0&length=-1');

        $response->assertOk();

        $this->assertLessThanOrEqual(100, count($response->json('data')));
    }

    public function test_records_endpoint_returns_empty_payload_for_unknown_type(): void
    {
        $user = $this->createSchoolHead();

        $response = $this->actingAs($user)->getJson('/dashboard/records?type=bogus&draw=3');

        $response->assertOk();

        $this->assertSame(3, $response->json('draw'));
        $this->assertSame(0, $response->json('recordsTotal'));
        $this->assertSame(0, $response->json('recordsFiltered'));
        $this->assertSame([], $response->json('data'));
    }

    public function test_records_endpoint_returns_classrooms_for_active_academic_year(): void
    {
        $user = $this->createSchoolHead();

        $academicYear = new AcademicYear;
        $academicYear->from = '2026';
        $academicYear->to = '2027';
        $academicYear->is_active = 1;
        $academicYear->save();

        $gradeLevel = new GradeLevel;
        $gradeLevel->level = 'Grade 1';
        $gradeLevel->save();

        $section = new Section;
        $section->section = 'A';
        $section->school_id = $this->school()->id;
        $section->save();

        $classroom = new Classroom;
        $classroom->section_id = $section->id;
        $classroom->grade_level_id = $gradeLevel->id;
        $classroom->academic_year_id = $academicYear->id;
        $classroom->school_id = $this->school()->id;
        $classroom->save();

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=classrooms&draw=1&start=0&length=10');

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertSame('Grade 1 - A', $response->json('data.0.record'));
        $this->assertSame('Grade 1', $response->json('data.0.detail'));
    }

    public function test_dashboard_page_renders_with_server_side_endpoint(): void
    {
        $user = $this->createSchoolHead();
        $this->createStudent('lrn-0001', 'Embedded');

        $this->createDivisionAndDistrict();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('lrn-0001');
        $response->assertSee('/dashboard/records', false);
    }

    public function test_dashboard_page_uses_no_data_table_api_removed_in_version_3(): void
    {
        $user = $this->createSchoolHead();

        $this->createDivisionAndDistrict();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();

        // DataTables 3 removed `column().searchable()`; the record type switch
        // now only relabels the header, because the endpoint decides which
        // columns are searchable.
        $response->assertDontSee('dataTable.column(0).searchable(', false);
        $response->assertSee('dataTable.column(0).title(titles[0]);', false);
    }

    public function test_a_school_head_sees_only_the_teachers_of_their_own_school(): void
    {
        $otherSchool = $this->createSecondSchool();

        $user = $this->createSchoolHead();
        $this->createUser('Teacher', ['last_name' => 'OwnSchool']);

        DB::table('tbl_teachers')->insert([
            'email' => 'other.school@deped.gov.ph',
            'user_id' => DB::table('tbl_users')->insertGetId([
                'username' => 'other.teacher@deped.gov.ph',
                'password' => Hash::make('password123'),
                'classification' => 'Teacher',
                'status' => 1,
                'person_id' => $this->createPerson()->id,
            ]),
            'school_id' => $otherSchool->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=teachers&draw=1&start=0&length=10');

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertStringContainsString('OwnSchool', $response->json('data.0.record'));
    }

    public function test_a_school_head_sees_only_the_students_of_their_own_school(): void
    {
        $otherSchool = $this->createSecondSchool();

        $user = $this->createSchoolHead();
        $this->createStudent('lrn-own', 'OwnSchool');
        $this->createStudentIn('lrn-other', 'OtherSchool', $otherSchool);

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=students&draw=1&start=0&length=10');

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertSame('lrn-own', $response->json('data.0.detail'));
    }

    public function test_a_school_head_sees_only_the_classrooms_of_their_own_school(): void
    {
        $otherSchool = $this->createSecondSchool();

        $user = $this->createSchoolHead();
        $this->createClassroomIn($this->school(), 'A');
        $this->createClassroomIn($otherSchool, 'Z');

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=classrooms&draw=1&start=0&length=10');

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertSame('Grade 1 - A', $response->json('data.0.record'));
    }

    /**
     * A teacher is not offered the teacher roster at all, so a hand crafted
     * query string must not widen the directory beyond their own classrooms.
     */
    public function test_a_teacher_is_not_offered_the_teacher_directory(): void
    {
        $user = $this->createUser('Teacher');
        $this->createUser('Teacher', ['last_name' => 'Someone']);

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=teachers&draw=4&start=0&length=10');

        $response->assertOk();

        $this->assertSame(4, $response->json('draw'));
        $this->assertSame(0, $response->json('recordsTotal'));
        $this->assertSame([], $response->json('data'));
    }

    public function test_a_teacher_only_sees_the_students_in_their_own_classrooms(): void
    {
        $otherSchool = $this->createSecondSchool();

        $teacher = $this->createUser('Teacher');
        $teacherId = Teacher::where('user_id', $teacher->id)->value('id');

        $ownClassroom = $this->createClassroomIn($this->school(), 'A');
        $otherClassroom = $this->createClassroomIn($otherSchool, 'Z');

        $subjectId = DB::table('tbl_subjects')->insertGetId(['title' => 'Mathematics']);

        DB::table('tbl_teacher_classes')->insert([
            'classroom_id' => $ownClassroom->id,
            'teacher_id' => $teacherId,
            'subject_id' => $subjectId,
            'advisory' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ownStudent = $this->createStudentIn('lrn-own', 'Inside');
        $outsideStudent = $this->createStudentIn('lrn-elsewhere', 'Outside');
        $otherClassStudent = $this->createStudentIn('lrn-other-class', 'OtherClass');

        foreach ([[$ownStudent->id, $ownClassroom->id], [$outsideStudent->id, $ownClassroom->id], [$otherClassStudent->id, $otherClassroom->id]] as [$studentId, $classroomId]) {
            DB::table('tbl_student_classrooms')->insert([
                'student_id' => $studentId,
                'classroom_id' => $classroomId,
                'status' => 1,
            ]);
        }

        $response = $this->actingAs($teacher)
            ->getJson('/dashboard/records?type=students&draw=1&start=0&length=10');

        $response->assertOk();

        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertStringNotContainsString('lrn-other-class', $response->getContent());
    }

    public function test_a_teacher_only_sees_their_assigned_classrooms(): void
    {
        $otherSchool = $this->createSecondSchool();

        $teacher = $this->createUser('Teacher');
        $teacherId = Teacher::where('user_id', $teacher->id)->value('id');

        $ownClassroom = $this->createClassroomIn($this->school(), 'A');
        $this->createClassroomIn($this->school(), 'B');
        $this->createClassroomIn($otherSchool, 'Z');

        DB::table('tbl_teacher_classes')->insert([
            'classroom_id' => $ownClassroom->id,
            'teacher_id' => $teacherId,
            'subject_id' => DB::table('tbl_subjects')->insertGetId(['title' => 'Mathematics']),
            'advisory' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($teacher)
            ->getJson('/dashboard/records?type=classrooms&draw=1&start=0&length=10');

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertSame('Grade 1 - A', $response->json('data.0.record'));
    }

    public function test_the_record_type_select_only_offers_the_types_the_role_may_see(): void
    {
        $this->createDivisionAndDistrict();

        $schoolHeadHtml = $this->actingAs($this->createSchoolHead())->get('/dashboard')->content();

        $this->assertStringContainsString('<option value="teachers">', $schoolHeadHtml);
        $this->assertStringContainsString('<option value="students">', $schoolHeadHtml);
        $this->assertStringContainsString('<option value="classrooms">', $schoolHeadHtml);

        $teacherHtml = $this->actingAs($this->createUser('Teacher'))->get('/dashboard')->content();

        $this->assertStringNotContainsString('<option value="teachers">', $teacherHtml);
        $this->assertStringContainsString('<option value="students">', $teacherHtml);
        $this->assertStringContainsString('<option value="classrooms">', $teacherHtml);
    }

    private function createDivisionAndDistrict(): void
    {
        $division = new Division;
        $division->name = 'Division One';
        $division->save();

        $district = new District;
        $district->name = 'District One';
        $district->division_id = $division->id;
        $district->save();

        $this->school()->district_id = $district->id;
        $this->school()->save();
    }

    private function createUser(string $classification, array $overrides = []): User
    {
        $person = new Person;
        $person->first_name = $overrides['first_name'] ?? 'Test';
        $person->middle_name = '';
        $person->last_name = $overrides['last_name'] ?? 'User'.($overrides['sequence'] ?? '');
        $person->suffix = '';
        $person->gender = 'M';
        $person->birth_date = '1990-01-01';
        $person->save();

        $user = new User;
        $user->username = $overrides['username'] ?? uniqid('user', true).'@deped.gov.ph';
        $user->password = Hash::make('password123');
        $user->classification = $classification;
        $user->status = true;
        $user->person_id = $person->id;
        $user->save();

        $teacher = new Teacher;
        $teacher->email = $user->username;
        $teacher->user_id = $user->id;
        $teacher->school_id = $this->school()->id;
        $teacher->save();

        return $user;
    }

    /**
     * A school head has no tbl_teachers row, which is exactly what made the
     * listings resolve a null school and return nothing.
     */
    private function createSchoolHead(?School $school = null): User
    {
        $school ??= $this->school();

        $user = new User;
        $user->username = uniqid('head', true).'@deped.gov.ph';
        $user->password = Hash::make('password123');
        $user->classification = 'School Head';
        $user->status = true;
        $user->person_id = $this->createPerson()->id;
        $user->save();

        DB::table('tbl_school_supervisors')->insert([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'status' => 1,
            'email' => $user->username,
        ]);

        return $user;
    }

    private function createSecondSchool(): School
    {
        $school = new School;
        $school->code = 'SANTA-MARIA-2';
        $school->name = 'Santa Maria Annex';
        $school->address = 'Santa Maria';
        $school->school_category_id = 1;
        $school->school_type_id = 1;
        $school->district_id = 1;
        $school->save();

        return $school;
    }

    private function createPerson(): Person
    {
        $person = new Person;
        $person->first_name = 'Test';
        $person->middle_name = '';
        $person->last_name = 'User';
        $person->suffix = '';
        $person->gender = 'M';
        $person->birth_date = '1990-01-01';
        $person->save();

        return $person;
    }

    private function createClassroomIn(School $school, string $section): Classroom
    {
        $academicYear = AcademicYear::where('is_active', 1)->first();

        if ($academicYear === null) {
            $academicYear = new AcademicYear;
            $academicYear->from = '2026';
            $academicYear->to = '2027';
            $academicYear->is_active = 1;
            $academicYear->save();
        }

        $gradeLevel = GradeLevel::where('level', 'Grade 1')->first();

        if ($gradeLevel === null) {
            $gradeLevel = new GradeLevel;
            $gradeLevel->level = 'Grade 1';
            $gradeLevel->save();
        }

        $sectionModel = Section::where('section', $section)
            ->where('school_id', $school->id)
            ->first();

        if ($sectionModel === null) {
            $sectionModel = new Section;
            $sectionModel->section = $section;
            $sectionModel->school_id = $school->id;
            $sectionModel->save();
        }

        $classroom = new Classroom;
        $classroom->section_id = $sectionModel->id;
        $classroom->grade_level_id = $gradeLevel->id;
        $classroom->academic_year_id = $academicYear->id;
        $classroom->school_id = $school->id;
        $classroom->save();

        return $classroom;
    }

    private function createStudentIn(string $lrn, string $lastName, ?School $school = null): Student
    {
        $school ??= $this->school();

        $person = new Person;
        $person->first_name = 'Juan';
        $person->middle_name = '';
        $person->last_name = $lastName;
        $person->suffix = '';
        $person->gender = 'M';
        $person->birth_date = '1995-01-01';
        $person->save();

        $user = new User;
        $user->username = uniqid('student', true).'@deped.gov.ph';
        $user->password = Hash::make('password123');
        $user->classification = 'Student';
        $user->status = true;
        $user->person_id = $person->id;
        $user->save();

        $student = new Student;
        $student->lrn = $lrn;
        $student->email = $user->username;
        $student->user_id = $user->id;
        $student->school_id = $school->id;
        $student->save();

        return $student;
    }

    private function createStudent(string $lrn, string $lastName): Student
    {
        return $this->createStudentIn($lrn, $lastName);
    }
}
