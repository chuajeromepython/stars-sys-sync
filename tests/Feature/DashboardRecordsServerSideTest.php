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
        $user = $this->createUser('Teacher');

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
        $user = $this->createUser('Teacher');

        foreach (['Alpha', 'Bravo', 'Charlie'] as $sequence) {
            $this->createUser('Teacher', ['last_name' => $sequence, 'sequence' => $sequence]);
        }

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=teachers&draw=1&start=0&length=2');

        $response->assertOk();

        $this->assertSame(4, $response->json('recordsTotal'));
        $this->assertSame(4, $response->json('recordsFiltered'));
        $this->assertCount(2, $response->json('data'));
        $this->assertArrayHasKey('record', $response->json('data.0'));
        $this->assertArrayHasKey('detail', $response->json('data.0'));
        $this->assertArrayHasKey('action', $response->json('data.0'));
    }

    public function test_records_endpoint_filters_students_by_search_term(): void
    {
        $user = $this->createUser('Teacher');
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
        $user = $this->createUser('Teacher');
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
        $user = $this->createUser('Teacher');
        $this->createStudent('lrn-0001', 'Matching');

        $response = $this->actingAs($user)->getJson(
            '/dashboard/records?type=students&draw=1&start=0&length=10&search[value]=btn-primary'
        );

        $response->assertOk();

        $this->assertSame(0, $response->json('recordsFiltered'));
    }

    public function test_records_endpoint_orders_students_by_last_name(): void
    {
        $user = $this->createUser('Teacher');
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
        $user = $this->createUser('Teacher');
        $this->createStudent('lrn-0001', 'Zulu');

        $response = $this->actingAs($user)->getJson(
            '/dashboard/records?type=students&draw=1&start=0&length=10&order[0][column]=99&order[0][dir]=asc'
        );

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_records_endpoint_handles_start_that_is_not_a_multiple_of_length(): void
    {
        $user = $this->createUser('Teacher');

        foreach (['Alpha', 'Bravo', 'Charlie'] as $sequence) {
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
        $user = $this->createUser('Teacher');

        $response = $this->actingAs($user)
            ->getJson('/dashboard/records?type=teachers&draw=1&start=0&length=-1');

        $response->assertOk();

        $this->assertLessThanOrEqual(100, count($response->json('data')));
    }

    public function test_records_endpoint_returns_empty_payload_for_unknown_type(): void
    {
        $user = $this->createUser('Teacher');

        $response = $this->actingAs($user)->getJson('/dashboard/records?type=bogus&draw=3');

        $response->assertOk();

        $this->assertSame(3, $response->json('draw'));
        $this->assertSame(0, $response->json('recordsTotal'));
        $this->assertSame(0, $response->json('recordsFiltered'));
        $this->assertSame([], $response->json('data'));
    }

    public function test_records_endpoint_returns_classrooms_for_active_academic_year(): void
    {
        $user = $this->createUser('Teacher');

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
        $user = $this->createUser('Teacher');
        $this->createStudent('lrn-0001', 'Embedded');

        $this->createDivisionAndDistrict();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('lrn-0001');
        $response->assertSee('/dashboard/records', false);
    }

    public function test_dashboard_page_uses_no_data_table_api_removed_in_version_3(): void
    {
        $user = $this->createUser('Teacher');

        $this->createDivisionAndDistrict();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();

        // DataTables 3 removed `column().searchable()`; the record type switch
        // now only relabels the header, because the endpoint decides which
        // columns are searchable.
        $response->assertDontSee('dataTable.column(0).searchable(', false);
        $response->assertSee('dataTable.column(0).title(titles[0]);', false);
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

    private function createStudent(string $lrn, string $lastName): Student
    {
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
        $student->school_id = $this->school()->id;
        $student->save();

        return $student;
    }
}
