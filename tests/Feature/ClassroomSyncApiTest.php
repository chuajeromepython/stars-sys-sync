<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClassroomSyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_returns_grouped_classrooms_for_valid_teacher_user_id(): void
    {
        $teacher_user_id = $this->seedTeacherWithClassrooms();
        $this->actingAs(User::findOrFail($teacher_user_id));

        $response = $this->postJson('/api/classrooms/sync', [
            'userId' => $teacher_user_id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Classrooms synced successfully',
            ]);

        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('Grade 1', $data);
        $this->assertCount(1, $data['Grade 1']);
        $this->assertSame('A', $data['Grade 1'][0]['section']);
    }

    public function test_sync_returns_validation_error_when_user_id_is_missing(): void
    {
        $response = $this->postJson('/api/classrooms/sync', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_sync_returns_forbidden_for_non_teacher_user(): void
    {
        $user_id = $this->seedNonTeacherUser();
        $this->actingAs(User::findOrFail($user_id));

        $this->postJson('/api/classrooms/sync', [
            'userId' => $user_id,
        ])->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only teacher accounts can sync classrooms.',
            ]);
    }

    public function test_sync_returns_empty_data_for_teacher_without_classrooms(): void
    {
        $teacher_user_id = $this->seedTeacherWithoutClassrooms();
        $this->actingAs(User::findOrFail($teacher_user_id));

        $response = $this->postJson('/api/classrooms/sync', [
            'userId' => $teacher_user_id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'No classrooms found',
                'data' => [],
            ]);
    }

    public function test_sync_returns_unauthorized_when_request_has_no_authenticated_session(): void
    {
        $teacher_user_id = $this->seedTeacherWithClassrooms();

        $this->postJson('/api/classrooms/sync', [
            'userId' => $teacher_user_id,
        ])->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated request. Please login first.',
            ]);
    }

    public function test_sync_returns_forbidden_when_user_id_does_not_match_active_session(): void
    {
        $teacher_user_id = $this->seedTeacherWithClassrooms();
        $other_teacher_user_id = $this->seedTeacherWithoutClassrooms();

        $this->actingAs(User::findOrFail($other_teacher_user_id));

        $this->postJson('/api/classrooms/sync', [
            'userId' => $teacher_user_id,
        ])->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Submitted userId does not match the active session.',
            ]);
    }

    private function seedTeacherWithClassrooms(): int
    {
        $teacher_person_id = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'John',
            'middle_name' => 'Q',
            'last_name' => 'Teacher',
            'suffix' => null,
            'gender' => 'M',
            'birth_date' => '1990-01-01',
        ]);

        $advisory_person_id = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Advisor',
            'suffix' => null,
            'gender' => 'F',
            'birth_date' => '1991-02-02',
        ]);

        $teacher_user_id = DB::table('tbl_users')->insertGetId([
            'username' => 'teacher.sync@example.test',
            'password' => bcrypt('password'),
            'classification' => 'Teacher',
            'status' => 1,
            'person_id' => $teacher_person_id,
        ]);

        $advisory_user_id = DB::table('tbl_users')->insertGetId([
            'username' => 'advisor.sync@example.test',
            'password' => bcrypt('password'),
            'classification' => 'Teacher',
            'status' => 1,
            'person_id' => $advisory_person_id,
        ]);

        $school_type_id = DB::table('tbl_school_types')->insertGetId([
            'type' => 'Elementary',
        ]);

        $school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-SYNC-001',
            'name' => 'Sync School',
            'address' => 'School Address',
            'school_category_id' => 1,
            'school_type_id' => $school_type_id,
            'district_id' => 1,
        ]);

        $academic_year_id = DB::table('tbl_academic_years')->insertGetId([
            'from' => '2025',
            'to' => '2026',
            'is_active' => 1,
        ]);

        $grade_level_id = DB::table('tbl_grade_levels')->insertGetId([
            'level' => 'Grade 1',
        ]);

        $subject_id = DB::table('tbl_subjects')->insertGetId([
            'title' => 'Mathematics',
        ]);

        $section_id = DB::table('tbl_sections')->insertGetId([
            'section' => 'A',
            'school_id' => $school_id,
        ]);

        $teacher_id = DB::table('tbl_teachers')->insertGetId([
            'email' => 'teacher.sync@example.test',
            'user_id' => $teacher_user_id,
            'school_id' => $school_id,
        ]);

        $advisory_teacher_id = DB::table('tbl_teachers')->insertGetId([
            'email' => 'advisor.sync@example.test',
            'user_id' => $advisory_user_id,
            'school_id' => $school_id,
        ]);

        $classroom_id = DB::table('tbl_classrooms')->insertGetId([
            'section_id' => $section_id,
            'grade_level_id' => $grade_level_id,
            'academic_year_id' => $academic_year_id,
            'school_id' => $school_id,
            'strand_id' => null,
            'course_id' => null,
            'track_id' => null,
            'semester_id' => null,
        ]);

        DB::table('tbl_teacher_classes')->insert([
            [
                'classroom_id' => $classroom_id,
                'teacher_id' => $teacher_id,
                'subject_id' => $subject_id,
                'advisory' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $classroom_id,
                'teacher_id' => $advisory_teacher_id,
                'subject_id' => $subject_id,
                'advisory' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return $teacher_user_id;
    }

    private function seedTeacherWithoutClassrooms(): int
    {
        $person_id = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Empty',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'suffix' => null,
            'gender' => 'M',
            'birth_date' => '1992-03-03',
        ]);

        $user_id = DB::table('tbl_users')->insertGetId([
            'username' => 'empty.teacher@example.test',
            'password' => bcrypt('password'),
            'classification' => 'Teacher',
            'status' => 1,
            'person_id' => $person_id,
        ]);

        $school_type_id = DB::table('tbl_school_types')->insertGetId([
            'type' => 'Elementary',
        ]);

        $school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-SYNC-002',
            'name' => 'Empty School',
            'address' => 'School Address',
            'school_category_id' => 1,
            'school_type_id' => $school_type_id,
            'district_id' => 1,
        ]);

        DB::table('tbl_academic_years')->insertGetId([
            'from' => '2026',
            'to' => '2027',
            'is_active' => 1,
        ]);

        DB::table('tbl_teachers')->insertGetId([
            'email' => 'empty.teacher@example.test',
            'user_id' => $user_id,
            'school_id' => $school_id,
        ]);

        return $user_id;
    }

    private function seedNonTeacherUser(): int
    {
        $person_id = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'School',
            'middle_name' => null,
            'last_name' => 'Head',
            'suffix' => null,
            'gender' => 'F',
            'birth_date' => '1989-04-04',
        ]);

        return DB::table('tbl_users')->insertGetId([
            'username' => 'head.sync@example.test',
            'password' => bcrypt('password'),
            'classification' => 'School Head',
            'status' => 1,
            'person_id' => $person_id,
        ]);
    }
}
