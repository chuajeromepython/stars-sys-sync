<?php

namespace Tests\Feature;

use App\Models\CustomFunction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomFunctionClassroomsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_classrooms_by_teacher_user_id_uses_a_fixed_query_count(): void
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
            'username' => 'teacher@example.test',
            'password' => bcrypt('password'),
            'classification' => 'Teacher',
            'status' => 1,
            'person_id' => $teacher_person_id,
        ]);

        $advisory_user_id = DB::table('tbl_users')->insertGetId([
            'username' => 'advisor@example.test',
            'password' => bcrypt('password'),
            'classification' => 'Teacher',
            'status' => 1,
            'person_id' => $advisory_person_id,
        ]);

        $school_type_id = DB::table('tbl_school_types')->insertGetId([
            'type' => 'Elementary',
        ]);

        $school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-001',
            'name' => 'Main School',
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

        $section_one_id = DB::table('tbl_sections')->insertGetId([
            'section' => 'A',
            'school_id' => $school_id,
        ]);

        $section_two_id = DB::table('tbl_sections')->insertGetId([
            'section' => 'B',
            'school_id' => $school_id,
        ]);

        $teacher_id = DB::table('tbl_teachers')->insertGetId([
            'email' => 'teacher@example.test',
            'user_id' => $teacher_user_id,
            'school_id' => $school_id,
        ]);

        $advisory_teacher_id = DB::table('tbl_teachers')->insertGetId([
            'email' => 'advisor@example.test',
            'user_id' => $advisory_user_id,
            'school_id' => $school_id,
        ]);

        $classroom_one_id = DB::table('tbl_classrooms')->insertGetId([
            'section_id' => $section_one_id,
            'grade_level_id' => $grade_level_id,
            'academic_year_id' => $academic_year_id,
            'school_id' => $school_id,
            'strand_id' => null,
            'course_id' => null,
            'track_id' => null,
            'semester_id' => null,
        ]);

        $classroom_two_id = DB::table('tbl_classrooms')->insertGetId([
            'section_id' => $section_two_id,
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
                'classroom_id' => $classroom_one_id,
                'teacher_id' => $teacher_id,
                'subject_id' => $subject_id,
                'advisory' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $classroom_two_id,
                'teacher_id' => $teacher_id,
                'subject_id' => $subject_id,
                'advisory' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $classroom_one_id,
                'teacher_id' => $advisory_teacher_id,
                'subject_id' => $subject_id,
                'advisory' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'classroom_id' => $classroom_two_id,
                'teacher_id' => $advisory_teacher_id,
                'subject_id' => $subject_id,
                'advisory' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs(User::find($teacher_user_id));

        DB::flushQueryLog();
        DB::enableQueryLog();

        $result = CustomFunction::getClassroomsByTeacherUserId($teacher_user_id);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(12, count($queries));
        $this->assertArrayHasKey('Grade 1', $result);
        $this->assertCount(2, $result['Grade 1']);
        $this->assertSame('A', $result['Grade 1'][0]['section']);
        $this->assertSame('B', $result['Grade 1'][1]['section']);
        $this->assertSame('Jane Advisor', $result['Grade 1'][0]['advisor']);
        $this->assertSame('Mathematics', $result['Grade 1'][0]['subject']);
    }
}
