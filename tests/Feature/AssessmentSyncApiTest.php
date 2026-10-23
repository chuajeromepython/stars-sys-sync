<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AssessmentSyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_returns_an_assessment_saved_with_the_legacy_user_id_reference(): void
    {
        DB::table('tbl_persons')->insert([
            'id' => 1,
            'first_name' => 'Teacher',
            'middle_name' => null,
            'last_name' => 'Forty Two',
            'gender' => 'M',
            'birth_date' => '1990-01-01',
        ]);

        DB::table('tbl_users')->insert([
            'id' => 42,
            'username' => 'teacher.42@example.test',
            'password' => bcrypt('password'),
            'classification' => 'Teacher',
            'status' => 1,
            'person_id' => 1,
        ]);

        DB::table('tbl_teachers')->insert([
            'id' => 11,
            'email' => 'teacher.42@example.test',
            'user_id' => 42,
            'school_id' => 1,
        ]);

        DB::table('tbl_academic_years')->insert([
            'id' => 1,
            'from' => '2026',
            'to' => '2027',
            'is_active' => 1,
        ]);

        DB::table('tbl_grade_levels')->insert([
            'id' => 1,
            'level' => 'Grade 1',
        ]);

        DB::table('tbl_subjects')->insert([
            'id' => 1,
            'title' => 'Mathematics',
        ]);

        DB::table('tbl_periods')->insert([
            'id' => 1,
            'period' => 'First',
        ]);

        DB::table('tbl_assessment_types')->insert([
            'id' => 1,
            'type' => 'Summative',
        ]);

        DB::table('tbl_assessments')->insert([
            'id' => 1,
            'title' => 'First Summative in Mathematics 1',
            'date' => '2026-10-21',
            'number_of_items' => 1,
            'assessment_type_id' => 1,
            'period_id' => 1,
            'grade_level_id' => 1,
            'subject_id' => 1,
            'teacher_id' => 42,
            'academic_year_id' => 1,
        ]);

        DB::table('tbl_ecdcs')->insert([
            'id' => 1,
            'classroom_id' => 1,
            'academic_year_id' => 1,
            'period' => '1',
            'teacher_id' => 11,
            'source' => '1',
            'date' => '2026-10-20',
        ]);

        $this->postJson('/api/assessment/sync', ['user_id' => 42])
            ->assertOk()
            ->assertJsonPath('data.assessments.0.id', 1)
            ->assertJsonPath('data.assessments.0.title', 'First Summative in Mathematics 1')
            ->assertJsonPath('data.ecdcs.0.id', 1)
            ->assertJsonPath('data.ecdcs.0.period_label', 'BOSY');
    }
}
