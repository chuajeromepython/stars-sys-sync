<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class ListingDataTableTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    private School $school;

    private School $otherSchool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();

        $this->school = $this->createSchool('School One');
        $this->otherSchool = $this->createSchool('School Two');
    }

    public function test_students_data_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/students/data?draw=1&start=0&length=10');

        $response->assertRedirect('/login');
    }

    public function test_students_data_endpoint_returns_paginated_rows(): void
    {
        $supervisor = $this->createSupervisor($this->school);

        $this->createStudent($this->school, 'lrn-0001', 'Zulu');
        $this->createStudent($this->school, 'lrn-0002', 'Alpha');

        $response = $this->actingAs($supervisor)
            ->getJson('/students/data?draw=1&start=0&length=10');

        $response->assertOk();

        $this->assertSame(1, $response->json('draw'));
        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertCount(2, $response->json('data'));
        $this->assertSame('lrn-0001', $response->json('data.0.lrn'));
        $this->assertStringContainsString('/students/', $response->json('data.0.action'));
    }

    public function test_students_data_endpoint_scopes_rows_to_the_supervisor_school(): void
    {
        $supervisor = $this->createSupervisor($this->school);

        $this->createStudent($this->school, 'lrn-0001', 'Mine');
        $this->createStudent($this->otherSchool, 'lrn-0002', 'Theirs');

        $response = $this->actingAs($supervisor)
            ->getJson('/students/data?draw=1&start=0&length=10');

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertSame('lrn-0001', $response->json('data.0.lrn'));
    }

    public function test_students_data_endpoint_filters_by_search_term(): void
    {
        $supervisor = $this->createSupervisor($this->school);

        $this->createStudent($this->school, 'lrn-0001', 'Bautista');
        $this->createStudent($this->school, 'lrn-0002', 'Ramos');

        $response = $this->actingAs($supervisor)
            ->getJson('/students/data?draw=1&start=0&length=10&search[value]=Bautista');

        $response->assertOk();

        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame('lrn-0001', $response->json('data.0.lrn'));
    }

    public function test_teachers_data_endpoint_scopes_rows_to_the_supervisor_school(): void
    {
        $supervisor = $this->createSupervisor($this->school);

        $this->createTeacherUser($this->school, 'mine@deped.gov.ph', 'Mine');
        $this->createTeacherUser($this->otherSchool, 'theirs@deped.gov.ph', 'Theirs');

        $response = $this->actingAs($supervisor)
            ->getJson('/teachers/data?draw=1&start=0&length=10');

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertSame('mine@deped.gov.ph', $response->json('data.0.username'));
    }

    public function test_teachers_data_endpoint_paginates_and_filters(): void
    {
        $supervisor = $this->createSupervisor($this->school);

        $this->createTeacherUser($this->school, 'alpha@deped.gov.ph', 'Alpha');
        $this->createTeacherUser($this->school, 'bravo@deped.gov.ph', 'Bravo');

        $response = $this->actingAs($supervisor)
            ->getJson('/teachers/data?draw=1&start=0&length=1');

        $response->assertOk();

        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertCount(1, $response->json('data'));

        $filtered = $this->actingAs($supervisor)
            ->getJson('/teachers/data?draw=2&start=0&length=10&search[value]=bravo@');

        $filtered->assertOk();

        $this->assertSame(1, $filtered->json('recordsFiltered'));
        $this->assertSame('bravo@deped.gov.ph', $filtered->json('data.0.username'));
    }

    public function test_listing_pages_render_empty_table_bodies(): void
    {
        $supervisor = $this->createSupervisor($this->school);

        $this->createStudent($this->school, 'lrn-0001', 'Hidden');

        $studentsResponse = $this->actingAs($supervisor)->get('/students');
        $studentsResponse->assertOk();
        $studentsResponse->assertDontSee('lrn-0001');
        $studentsResponse->assertSee('id="dt_students"', false);

        $teachersResponse = $this->actingAs($supervisor)->get('/teachers');
        $teachersResponse->assertOk();
        $teachersResponse->assertSee('id="dt_teachers"', false);
    }

    private function createSchool(string $name): School
    {
        $school = new School;
        $school->code = strtoupper(str_replace(' ', '-', $name));
        $school->name = $name;
        $school->address = $name;
        $school->school_category_id = 1;
        $school->school_type_id = 1;
        $school->district_id = 1;
        $school->save();

        return $school;
    }

    private function createSupervisor(School $school): User
    {
        $user = $this->createUser($school, 'supervisor@deped.gov.ph', 'School Head');

        $supervisor = new SchoolSupervisor;
        $supervisor->user_id = $user->id;
        $supervisor->school_id = $school->id;
        $supervisor->status = 1;
        $supervisor->email = $user->username;
        $supervisor->save();

        return $user;
    }

    private function createTeacherUser(School $school, string $username, string $lastName): User
    {
        $user = $this->createUser($school, $username, 'Teacher', $lastName);

        $teacher = new Teacher;
        $teacher->email = $username;
        $teacher->user_id = $user->id;
        $teacher->school_id = $school->id;
        $teacher->save();

        return $user;
    }

    private function createUser(School $school, string $username, string $classification, string $lastName = 'User'): User
    {
        $person = new Person;
        $person->first_name = 'Test';
        $person->middle_name = '';
        $person->last_name = $lastName;
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

        return $user;
    }

    private function createStudent(School $school, string $lrn, string $lastName): Student
    {
        $user = $this->createUser($school, $lrn.'@student.test', 'Student', $lastName);

        $student = new Student;
        $student->lrn = $lrn;
        $student->email = $user->username;
        $student->user_id = $user->id;
        $student->school_id = $school->id;
        $student->save();

        return $student;
    }
}
