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

class DashboardStatisticsTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    public function test_teacher_statistics_include_only_assigned_students_for_the_selected_year(): void
    {
        [$activeYear, $oldYear, , , $school] = $this->dashboardHierarchy();
        $teacherUser = $this->userWithRole(Role::Teacher);
        $teacher = $this->createTeacher($teacherUser, $school);

        $activeAdvisory = $this->createClassroom($school, $activeYear);
        $activeSubject = $this->createClassroom($school, $activeYear);
        $oldClassroom = $this->createClassroom($school, $oldYear);
        $this->assignTeacher($teacher, $activeAdvisory, true);
        $this->assignTeacher($teacher, $activeSubject, false);
        $this->assignTeacher($teacher, $oldClassroom, true);

        $sharedStudent = $this->createStudent($school, 'LRN-1');
        $subjectStudent = $this->createStudent($school, 'LRN-2');
        $oldStudent = $this->createStudent($school, 'LRN-OLD');
        $this->enrol($sharedStudent, $activeAdvisory);
        $this->enrol($sharedStudent, $activeSubject);
        $this->enrol($subjectStudent, $activeSubject);
        $this->enrol($oldStudent, $oldClassroom);

        $response = $this->actingAs($teacherUser)->get('/dashboard');
        $statistics = collect($response->viewData('statistics'))->keyBy('label');

        $response->assertOk();
        $this->assertSame(1, $statistics['Advisory Students']['value']);
        $this->assertSame(2, $statistics['Subject Class Students']['value']);
        $this->assertSame(2, $statistics['Students']['value']);

        $oldResponse = $this->actingAs($teacherUser)->get('/dashboard?academic_year_id='.$oldYear->id);
        $oldStatistics = collect($oldResponse->viewData('statistics'))->keyBy('label');

        $this->assertSame(1, $oldStatistics['Students']['value']);
        $oldResponse->assertSee('value="'.$oldYear->id.'" selected', false);
    }

    public function test_school_head_student_statistics_are_limited_to_their_school_and_year(): void
    {
        [$activeYear, $oldYear, , , $school, $otherSchool] = $this->dashboardHierarchy();
        $schoolHead = $this->userWithRole(Role::SchoolHead);
        DB::table('tbl_school_supervisors')->insert([
            'user_id' => $schoolHead->id,
            'school_id' => $school->id,
            'status' => 1,
            'email' => $schoolHead->username,
        ]);

        $ownClassroom = $this->createClassroom($school, $activeYear);
        $oldClassroom = $this->createClassroom($school, $oldYear);
        $otherClassroom = $this->createClassroom($otherSchool, $activeYear);
        $this->enrol($this->createStudent($school, 'OWN'), $ownClassroom);
        $this->enrol($this->createStudent($school, 'OLD'), $oldClassroom);
        $this->enrol($this->createStudent($otherSchool, 'OTHER'), $otherClassroom);

        $statistics = collect($this->actingAs($schoolHead)->get('/dashboard')->viewData('statistics'))->keyBy('label');

        $this->assertSame(1, $statistics['Students']['value']);
        $this->assertSame(1, $statistics['Classrooms']['value']);
    }

    public function test_district_supervisor_student_statistics_are_limited_to_their_district_and_year(): void
    {
        [$activeYear, , , $district, $school, $otherSchool] = $this->dashboardHierarchy();
        $districtSupervisor = $this->userWithRole(Role::DistrictSupervisor);
        DB::table('tbl_district_supervisors')->insert([
            'user_id' => $districtSupervisor->id,
            'district_id' => $district->id,
            'status' => 1,
            'email' => $districtSupervisor->username,
        ]);

        $ownClassroom = $this->createClassroom($school, $activeYear);
        $otherClassroom = $this->createClassroom($otherSchool, $activeYear);
        $this->enrol($this->createStudent($school, 'OWN'), $ownClassroom);
        $this->enrol($this->createStudent($otherSchool, 'OTHER'), $otherClassroom);

        $statistics = collect($this->actingAs($districtSupervisor)->get('/dashboard')->viewData('statistics'))->keyBy('label');

        $this->assertSame(1, $statistics['Students']['value']);
    }

    /**
     * @return array{AcademicYear, AcademicYear, Division, District, School, School}
     */
    private function dashboardHierarchy(): array
    {
        $activeYear = $this->createAcademicYear('2026', '2027', true);
        $oldYear = $this->createAcademicYear('2025', '2026', false);
        $division = new Division;
        $division->name = 'Division One';
        $division->save();
        $district = new District;
        $district->name = 'District One';
        $district->division_id = $division->id;
        $district->save();
        $otherDistrict = new District;
        $otherDistrict->name = 'District Two';
        $otherDistrict->division_id = $division->id;
        $otherDistrict->save();

        return [$activeYear, $oldYear, $division, $district, $this->createSchool($district, 'ONE'), $this->createSchool($otherDistrict, 'TWO')];
    }

    private function createAcademicYear(string $from, string $to, bool $active): AcademicYear
    {
        $year = new AcademicYear;
        $year->from = $from;
        $year->to = $to;
        $year->is_active = $active;
        $year->save();

        return $year;
    }

    private function createSchool(District $district, string $suffix): School
    {
        $school = new School;
        $school->code = 'SCHOOL-'.$suffix;
        $school->name = 'School '.$suffix;
        $school->address = 'Address';
        $school->school_category_id = 1;
        $school->school_type_id = 1;
        $school->district_id = $district->id;
        $school->save();

        return $school;
    }

    private function createTeacher(User $user, School $school): Teacher
    {
        $teacher = new Teacher;
        $teacher->user_id = $user->id;
        $teacher->school_id = $school->id;
        $teacher->email = $user->username;
        $teacher->save();

        return $teacher;
    }

    private function createClassroom(School $school, AcademicYear $year): Classroom
    {
        $classroom = new Classroom;
        $classroom->section_id = DB::table('tbl_sections')->insertGetId(['section' => uniqid('Section-'), 'school_id' => $school->id]);
        $classroom->grade_level_id = DB::table('tbl_grade_levels')->insertGetId(['level' => uniqid('Grade-')]);
        $classroom->academic_year_id = $year->id;
        $classroom->school_id = $school->id;
        $classroom->save();

        return $classroom;
    }

    private function assignTeacher(Teacher $teacher, Classroom $classroom, bool $advisory): void
    {
        DB::table('tbl_teacher_classes')->insert([
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'subject_id' => DB::table('tbl_subjects')->insertGetId(['title' => uniqid('Subject-')]),
            'advisory' => $advisory,
        ]);
    }

    private function createStudent(School $school, string $lrn): Student
    {
        $user = $this->userWithRole(Role::Student, ['username' => strtolower($lrn).'@test.local']);
        $student = new Student;
        $student->user_id = $user->id;
        $student->school_id = $school->id;
        $student->lrn = $lrn;
        $student->email = strtolower($lrn).'@student.test';
        $student->save();

        return $student;
    }

    private function enrol(Student $student, Classroom $classroom): void
    {
        DB::table('tbl_student_classrooms')->insert([
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'status' => 1,
            'is_uploaded' => 1,
        ]);
    }
}
