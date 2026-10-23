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

    public function test_resolve_school_id_prefers_the_school_supervisor_profile(): void
    {
        $person_id = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Ana',
            'middle_name' => null,
            'last_name' => 'Head',
            'suffix' => null,
            'gender' => 'F',
            'birth_date' => '1985-05-05',
        ]);

        $user_id = DB::table('tbl_users')->insertGetId([
            'username' => 'head.resolve@example.test',
            'password' => bcrypt('password'),
            // Deliberately not "School Head": resolution must not depend on it.
            'classification' => 'Department Head',
            'status' => 1,
            'person_id' => $person_id,
        ]);

        $school_type_id = DB::table('tbl_school_types')->insertGetId(['type' => 'Elementary']);

        $school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-RESOLVE',
            'name' => 'Resolve School',
            'address' => 'Somewhere',
            'school_category_id' => 1,
            'school_type_id' => $school_type_id,
            'district_id' => 1,
        ]);

        DB::table('tbl_school_supervisors')->insert([
            'user_id' => $user_id,
            'school_id' => $school_id,
            'status' => 1,
            'email' => 'head.resolve@example.test',
        ]);

        $this->assertSame(
            $school_id,
            CustomFunction::resolveSchoolIdForUser(User::findOrFail($user_id))
        );
    }

    public function test_resolve_school_id_falls_back_to_the_teacher_profile(): void
    {
        $person_id = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Ben',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'suffix' => null,
            'gender' => 'M',
            'birth_date' => '1986-06-06',
        ]);

        $user_id = DB::table('tbl_users')->insertGetId([
            'username' => 'teacher.resolve@example.test',
            'password' => bcrypt('password'),
            'classification' => 'Teacher',
            'status' => 1,
            'person_id' => $person_id,
        ]);

        $school_type_id = DB::table('tbl_school_types')->insertGetId(['type' => 'Elementary']);

        $school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-RESOLVE-2',
            'name' => 'Teacher School',
            'address' => 'Somewhere',
            'school_category_id' => 1,
            'school_type_id' => $school_type_id,
            'district_id' => 1,
        ]);

        DB::table('tbl_teachers')->insert([
            'email' => 'teacher.resolve@example.test',
            'user_id' => $user_id,
            'school_id' => $school_id,
        ]);

        $this->assertSame(
            $school_id,
            CustomFunction::resolveSchoolIdForUser(User::findOrFail($user_id))
        );
    }

    public function test_resolve_school_id_returns_null_for_a_division_level_office(): void
    {
        $person_id = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Cara',
            'middle_name' => null,
            'last_name' => 'Admin',
            'suffix' => null,
            'gender' => 'F',
            'birth_date' => '1987-07-07',
        ]);

        $user_id = DB::table('tbl_users')->insertGetId([
            'username' => 'admin.resolve@example.test',
            'password' => bcrypt('password'),
            'classification' => 'Division Administrator',
            'status' => 1,
            'person_id' => $person_id,
        ]);

        // The old implementation fell through to the supervisor query for any
        // non teacher, producing a null school and a broken "var school_id = ;".
        $this->assertNull(CustomFunction::resolveSchoolIdForUser(User::findOrFail($user_id)));
    }

    /**
     * A school head has no tbl_teachers row, so the teacher scoped listing could
     * never return their classrooms and the Classroom page showed nothing at
     * all. getClassrooms() now falls back to the school scoped query for them.
     */
    public function test_get_classrooms_returns_the_whole_school_for_a_non_teacher(): void
    {
        $person_id = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Ana',
            'middle_name' => null,
            'last_name' => 'Head',
            'suffix' => null,
            'gender' => 'F',
            'birth_date' => '1980-05-05',
        ]);

        $user_id = DB::table('tbl_users')->insertGetId([
            'username' => 'head.classrooms@example.test',
            'password' => bcrypt('password'),
            'classification' => 'School Head',
            'status' => 1,
            'person_id' => $person_id,
        ]);

        $school_type_id = DB::table('tbl_school_types')->insertGetId(['type' => 'Elementary']);

        $school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-HEAD',
            'name' => 'Head School',
            'address' => 'Somewhere',
            'school_category_id' => 1,
            'school_type_id' => $school_type_id,
            'district_id' => 1,
        ]);

        $academic_year_id = DB::table('tbl_academic_years')->insertGetId([
            'from' => '2025',
            'to' => '2026',
            'is_active' => 1,
        ]);

        $grade_level_id = DB::table('tbl_grade_levels')->insertGetId(['level' => 'Grade 1']);

        $section_id = DB::table('tbl_sections')->insertGetId([
            'section' => 'A',
            'school_id' => $school_id,
        ]);

        // No advisory row on purpose: a school head must still see a room that
        // simply has no advisor assigned yet.
        DB::table('tbl_classrooms')->insert([
            'section_id' => $section_id,
            'grade_level_id' => $grade_level_id,
            'academic_year_id' => $academic_year_id,
            'school_id' => $school_id,
            'strand_id' => null,
            'course_id' => null,
            'track_id' => null,
            'semester_id' => null,
        ]);

        DB::table('tbl_school_supervisors')->insert([
            'user_id' => $user_id,
            'school_id' => $school_id,
            'status' => 1,
            'email' => 'head.classrooms@example.test',
        ]);

        $this->actingAs(User::findOrFail($user_id));

        $result = CustomFunction::getClassrooms();

        $this->assertArrayHasKey('Grade 1', $result);
        $this->assertCount(1, $result['Grade 1']);
        $this->assertSame('A', $result['Grade 1'][0]['section']);
        $this->assertSame('N/A', $result['Grade 1'][0]['advisor']);
    }

    /**
     * A school must never see the classrooms of another school, whichever
     * school scoped role is asking.
     */
    public function test_get_classrooms_for_school_excludes_another_schools_rooms(): void
    {
        $school_type_id = DB::table('tbl_school_types')->insertGetId(['type' => 'Elementary']);

        $own_school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-OWN',
            'name' => 'Own School',
            'address' => 'Somewhere',
            'school_category_id' => 1,
            'school_type_id' => $school_type_id,
            'district_id' => 1,
        ]);

        $other_school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-OTHER',
            'name' => 'Other School',
            'address' => 'Elsewhere',
            'school_category_id' => 1,
            'school_type_id' => $school_type_id,
            'district_id' => 1,
        ]);

        $academic_year_id = DB::table('tbl_academic_years')->insertGetId([
            'from' => '2025',
            'to' => '2026',
            'is_active' => 1,
        ]);

        $grade_level_id = DB::table('tbl_grade_levels')->insertGetId(['level' => 'Grade 1']);

        $own_section_id = DB::table('tbl_sections')->insertGetId([
            'section' => 'A',
            'school_id' => $own_school_id,
        ]);

        $other_section_id = DB::table('tbl_sections')->insertGetId([
            'section' => 'Z',
            'school_id' => $other_school_id,
        ]);

        foreach ([[$own_section_id, $own_school_id], [$other_section_id, $other_school_id]] as [$sectionId, $schoolId]) {
            DB::table('tbl_classrooms')->insert([
                'section_id' => $sectionId,
                'grade_level_id' => $grade_level_id,
                'academic_year_id' => $academic_year_id,
                'school_id' => $schoolId,
                'strand_id' => null,
                'course_id' => null,
                'track_id' => null,
                'semester_id' => null,
            ]);
        }

        $result = CustomFunction::getClassroomsForSchool($own_school_id);

        $this->assertArrayHasKey('Grade 1', $result);
        $this->assertCount(1, $result['Grade 1']);
        $this->assertSame('A', $result['Grade 1'][0]['section']);
    }
}
