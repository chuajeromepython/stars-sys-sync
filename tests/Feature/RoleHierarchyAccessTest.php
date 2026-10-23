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
use App\Services\DataTable\RoleDashboardDirectory;
use App\Support\RoleDataScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class RoleHierarchyAccessTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    public function test_superintendent_dashboard_counts_only_the_assigned_division(): void
    {
        [$division, $district, $school] = $this->createOrganization('North');
        [, , $otherSchool] = $this->createOrganization('South');

        $superintendent = $this->userWithRole(Role::DivisionSuperintendent);
        DB::table('tbl_division_superintendents')->insert([
            'user_id' => $superintendent->id,
            'division_id' => $division->id,
            'status' => 1,
            'email' => $superintendent->username,
        ]);

        $year = $this->createActiveYear();
        $ownClassroom = $this->createClassroom($school, $year);
        $otherClassroom = $this->createClassroom($otherSchool, $year);
        $ownStudent = $this->createStudent($school, 'NORTH-LRN');
        $otherStudent = $this->createStudent($otherSchool, 'SOUTH-LRN');
        $this->enrol($ownStudent, $ownClassroom);
        $this->enrol($otherStudent, $otherClassroom);

        $response = $this->actingAs($superintendent)->get('/dashboard');
        $metrics = collect($response->viewData('dashboard')['metrics'])->keyBy('label');
        $distributionLabels = collect($response->viewData('dashboard')['distribution'])
            ->pluck('label')
            ->all();

        $response->assertOk();
        $response->assertSee('Division Performance Overview');
        $response->assertSee('Welcome, Test User.');
        $response->assertSee('Read only');
        $this->assertSame('North Division', $response->viewData('welcomeContext')['division']);
        $this->assertSame(1, $metrics['Schools']['value']);
        $this->assertSame(1, $metrics['Districts']['value']);
        $this->assertSame(1, $metrics['Students']['value']);
        $this->assertSame(1, $metrics['Classrooms']['value']);
        $this->assertSame([$district->name], $distributionLabels);
        $this->assertNotContains($otherSchool->name, $distributionLabels);
    }

    public function test_school_reports_are_generated_separately_for_selected_authorized_schools(): void
    {
        [$division, $district, $firstSchool] = $this->createOrganization('North');
        $secondSchool = $this->createSchool($district, 'North-Second');
        $supervisor = $this->userWithRole(Role::DivisionSupervisor);
        DB::table('tbl_division_supervisors')->insert([
            'user_id' => $supervisor->id,
            'division_id' => $division->id,
            'subject_id' => '',
            'status' => 1,
            'email' => $supervisor->username,
        ]);

        $filters = $this->reportFilters();
        $response = $this->actingAs($supervisor)->postJson('/reports/generate', $filters + [
            'school_ids' => [$firstSchool->id, $secondSchool->id],
        ]);

        $response->assertOk();
        $response->assertJsonCount(2, 'reports');
        $response->assertJsonPath('reports.0.school_name', $firstSchool->name);
        $response->assertJsonPath('reports.1.school_name', $secondSchool->name);
        $response->assertJsonPath('reports.0.report', []);
        $response->assertJsonPath('reports.1.report', []);
    }

    public function test_report_generation_hides_schools_outside_the_assigned_division(): void
    {
        [$division] = $this->createOrganization('North');
        [, , $otherSchool] = $this->createOrganization('South');
        $supervisor = $this->userWithRole(Role::DivisionSupervisor);
        DB::table('tbl_division_supervisors')->insert([
            'user_id' => $supervisor->id,
            'division_id' => $division->id,
            'subject_id' => '',
            'status' => 1,
            'email' => $supervisor->username,
        ]);

        $response = $this->actingAs($supervisor)->postJson('/reports/generate', $this->reportFilters() + [
            'school_ids' => [$otherSchool->id],
        ]);

        $response->assertNotFound();
    }

    public function test_report_generation_requires_at_least_one_school(): void
    {
        [$division] = $this->createOrganization('North');
        $supervisor = $this->userWithRole(Role::DivisionSupervisor);
        DB::table('tbl_division_supervisors')->insert([
            'user_id' => $supervisor->id,
            'division_id' => $division->id,
            'subject_id' => '',
            'status' => 1,
            'email' => $supervisor->username,
        ]);

        $response = $this->actingAs($supervisor)->postJson('/reports/generate', $this->reportFilters());

        $response->assertUnprocessable();
        $response->assertInvalid(['school_ids']);
    }

    public function test_division_supervisor_sees_only_assigned_division_districts(): void
    {
        [$division, $district] = $this->createOrganization('North');
        [, $otherDistrict] = $this->createOrganization('South');
        $supervisor = $this->userWithRole(Role::DivisionSupervisor);
        DB::table('tbl_division_supervisors')->insert([
            'user_id' => $supervisor->id,
            'division_id' => $division->id,
            'subject_id' => '',
            'status' => 1,
            'email' => $supervisor->username,
        ]);

        $response = $this->actingAs($supervisor)->get('/districts');

        $response->assertOk();
        $this->assertSame(
            [$district->id],
            $response->viewData('districts')->pluck('id')->all()
        );
        $response->assertDontSee($otherDistrict->name);
    }

    public function test_district_supervisor_sees_only_assigned_district_schools(): void
    {
        DB::table('tbl_school_categories')->updateOrInsert(
            ['id' => 1],
            ['category' => 'Public']
        );
        DB::table('tbl_school_types')->updateOrInsert(
            ['id' => 1],
            ['type' => 'Elementary']
        );
        [, $district, $school] = $this->createOrganization('North');
        [, $otherDistrict, $otherSchool] = $this->createOrganization('South');
        $supervisor = $this->userWithRole(Role::DistrictSupervisor);
        DB::table('tbl_district_supervisors')->insert([
            'user_id' => $supervisor->id,
            'district_id' => $district->id,
            'status' => 1,
            'email' => $supervisor->username,
        ]);

        $response = $this->actingAs($supervisor)->get('/schools');

        $response->assertOk();
        $this->assertSame([$school->id], $response->viewData('schools')->pluck('id')->all());
        $response->assertDontSee($otherSchool->name);
        $response->assertDontSee('Add School');
        $this->assertNotContains($otherDistrict->id, $response->viewData('schools')->pluck('district_id')->all());

        $districtResponse = $this->actingAs($supervisor)->get('/districts');

        $districtResponse->assertOk();
        $this->assertSame([$district->id], $districtResponse->viewData('districts')->pluck('id')->all());
        $districtResponse->assertDontSee('Add District');
    }

    public function test_division_supervisor_cannot_update_a_district_outside_the_assigned_division(): void
    {
        [$division] = $this->createOrganization('North');
        [, $otherDistrict] = $this->createOrganization('South');
        $supervisor = $this->userWithRole(Role::DivisionSupervisor);
        DB::table('tbl_division_supervisors')->insert([
            'user_id' => $supervisor->id,
            'division_id' => $division->id,
            'subject_id' => '',
            'status' => 1,
            'email' => $supervisor->username,
        ]);

        $this->actingAs($supervisor)
            ->post('/districts/update', [
                'id' => $otherDistrict->id,
                'name' => 'Unauthorized Change',
                'division_id' => $otherDistrict->division_id,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('tbl_districts', [
            'id' => $otherDistrict->id,
            'name' => 'South District',
        ]);
    }

    public function test_division_supervisor_cannot_load_districts_for_another_division(): void
    {
        [$division] = $this->createOrganization('North');
        [$otherDivision] = $this->createOrganization('South');
        $supervisor = $this->userWithRole(Role::DivisionSupervisor);
        DB::table('tbl_division_supervisors')->insert([
            'user_id' => $supervisor->id,
            'division_id' => $division->id,
            'subject_id' => '',
            'status' => 1,
            'email' => $supervisor->username,
        ]);

        $this->actingAs($supervisor)
            ->postJson('/getDistrictsPerDivision', ['division_id' => $otherDivision->id])
            ->assertNotFound();
    }

    public function test_division_supervisor_cannot_create_a_school_in_another_division(): void
    {
        [$division] = $this->createOrganization('North');
        [, $otherDistrict] = $this->createOrganization('South');
        $supervisor = $this->userWithRole(Role::DivisionSupervisor);
        DB::table('tbl_division_supervisors')->insert([
            'user_id' => $supervisor->id,
            'division_id' => $division->id,
            'subject_id' => '',
            'status' => 1,
            'email' => $supervisor->username,
        ]);

        $this->actingAs($supervisor)
            ->post('/schools/store', [
                'name' => 'Out of Scope School',
                'address' => 'Address',
                'code' => 'OUT-OF-SCOPE',
                'category_id' => 1,
                'type_id' => 1,
                'district_id' => $otherDistrict->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('tbl_schools', ['code' => 'OUT-OF-SCOPE']);
    }

    public function test_division_administrator_cannot_edit_a_school_outside_the_assigned_division(): void
    {
        [$division] = $this->createOrganization('North');
        [, , $otherSchool] = $this->createOrganization('South');
        $administrator = $this->userWithRole(Role::DivisionAdministrator);
        DB::table('tbl_division_administrators')->insert([
            'user_id' => $administrator->id,
            'division_id' => $division->id,
            'status' => 1,
            'email' => $administrator->username,
        ]);

        $this->actingAs($administrator)
            ->get('/schools/'.$otherSchool->id.'/edit')
            ->assertNotFound();
    }

    public function test_student_without_report_permission_cannot_generate_reports(): void
    {
        $student = $this->userWithRole(Role::Student);

        $this->actingAs($student)
            ->post('/reports/generate')
            ->assertRedirect('/forbidden');
    }

    public function test_multiple_permissions_do_not_widen_a_teachers_assigned_school_scope(): void
    {
        [, $district, $school] = $this->createOrganization('North');
        $secondSchool = $this->createSchool($district, 'North-Second');
        $teacherUser = $this->userWithRole(Role::Teacher);
        $teacher = new Teacher;
        $teacher->user_id = $teacherUser->id;
        $teacher->school_id = $school->id;
        $teacher->email = $teacherUser->username;
        $teacher->save();
        $teacherUser->assignRole(Role::DivisionSuperintendent->value);

        $this->assertTrue($teacherUser->can('schools.view'));
        $this->assertSame([$school->id], app(RoleDataScope::class)->schoolIdsFor($teacherUser));
        $this->assertNotContains($secondSchool->id, app(RoleDataScope::class)->schoolIdsFor($teacherUser));
    }

    public function test_a_student_dashboard_directory_is_limited_to_the_signed_in_student(): void
    {
        [, , $school] = $this->createOrganization('North');
        $studentUser = $this->userWithRole(Role::Student);
        $studentUser->assignRole(Role::DivisionSuperintendent->value);
        $ownStudent = $this->createStudent($school, 'OWN-LRN', $studentUser);
        $this->createStudent($school, 'OTHER-LRN');

        $resolved = app(RoleDashboardDirectory::class)->resolve('students', $studentUser, null);
        $rows = $resolved['query']->get();

        $this->assertCount(1, $rows);
        $this->assertSame($ownStudent->id, $rows->first()->id);
    }

    public function test_chief_permissions_are_domain_specific_and_leadership_is_read_only(): void
    {
        $cid = $this->userWithRole(Role::ChiefOfCID);
        $sgod = $this->userWithRole(Role::ChiefOfSGOD);
        $superintendent = $this->userWithRole(Role::DivisionSuperintendent);
        $administrator = $this->userWithRole(Role::DivisionAdministrator);

        $this->assertTrue($cid->can('subjects.view'));
        $this->assertFalse($cid->can('districts.view'));
        $this->assertTrue($sgod->can('districts.view'));
        $this->assertFalse($sgod->can('subjects.view'));
        $this->assertFalse($superintendent->can('schools.manage'));
        $this->assertTrue($administrator->can('roles.manage'));
        $this->assertTrue($administrator->can('permissions.manage'));
    }

    /**
     * @return array{Division, District, School}
     */
    private function createOrganization(string $prefix): array
    {
        $division = new Division;
        $division->name = $prefix.' Division';
        $division->save();

        $district = new District;
        $district->name = $prefix.' District';
        $district->division_id = $division->id;
        $district->save();

        return [$division, $district, $this->createSchool($district, $prefix.'-School')];
    }

    private function createSchool(District $district, string $name): School
    {
        $school = new School;
        $school->code = str($name)->slug()->upper()->toString();
        $school->name = $name;
        $school->address = $name.' Address';
        $school->school_category_id = 1;
        $school->school_type_id = 1;
        $school->district_id = $district->id;
        $school->save();

        return $school;
    }

    private function createActiveYear(): AcademicYear
    {
        $year = new AcademicYear;
        $year->from = '2026';
        $year->to = '2027';
        $year->is_active = 1;
        $year->save();

        return $year;
    }

    private function createClassroom(School $school, AcademicYear $year): Classroom
    {
        $classroom = new Classroom;
        $classroom->section_id = DB::table('tbl_sections')->insertGetId([
            'section' => 'Section '.uniqid(),
            'school_id' => $school->id,
        ]);
        $classroom->grade_level_id = DB::table('tbl_grade_levels')->insertGetId([
            'level' => 'Grade '.uniqid(),
        ]);
        $classroom->academic_year_id = $year->id;
        $classroom->school_id = $school->id;
        $classroom->save();

        return $classroom;
    }

    private function createStudent(School $school, string $lrn, ?User $user = null): Student
    {
        $user ??= $this->userWithRole(Role::Student, ['username' => strtolower($lrn).'@test.local']);

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

    /**
     * @return array<string, int|string>
     */
    private function reportFilters(): array
    {
        return [
            'education_level_id' => 1,
            'period_id' => DB::table('tbl_periods')->insertGetId(['period' => 'Quarter 1']),
            'grade_level_id' => DB::table('tbl_grade_levels')->insertGetId(['level' => 'Grade 1']),
            'subject_id' => DB::table('tbl_subjects')->insertGetId(['title' => 'Mathematics']),
            'assessment_type_id' => DB::table('tbl_assessment_types')->insertGetId(['type' => 'Summative']),
            'assessment_date' => '2026-08-01',
            'report_type_id' => 'LC',
        ];
    }
}
