<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Controllers\ReportController;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\ClassAssessment;
use App\Models\Classroom;
use App\Models\District;
use App\Models\Division;
use App\Models\School;
use App\Models\Teacher;
use App\Models\TeacherClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    public function test_teacher_assessment_type_options_include_diagnostic(): void
    {
        DB::table('tbl_assessment_types')->insert([
            ['type' => 'Summative'], ['type' => 'Term Exam'],
            ['type' => 'Diagnostic'], ['type' => 'ECD'],
        ]);

        $response = $this->actingAs($this->userWithRole(Role::Teacher))
            ->postJson('/getAssessmentTypePerGradeLevel', ['grade_level_id' => 1]);

        $response->assertOk();
        $this->assertSame(
            ['Summative', 'Term Exam', 'Diagnostic'],
            array_column($response->json(), 'type')
        );
    }

    public function test_report_hierarchy_dropdowns_are_populated_for_assigned_accounts(): void
    {
        [$division, $district, $school] = $this->hierarchy();
        $teacherUser = $this->userWithRole(Role::Teacher);
        $teacher = new Teacher;
        $teacher->user_id = $teacherUser->id;
        $teacher->school_id = $school->id;
        $teacher->email = $teacherUser->username;
        $teacher->save();

        $accounts = [
            [Role::SchoolHead, 'tbl_school_supervisors', 'school_id', $school->id],
            [Role::DistrictSupervisor, 'tbl_district_supervisors', 'district_id', $district->id],
            [Role::DivisionSupervisor, 'tbl_division_supervisors', 'division_id', $division->id],
            [Role::ChiefOfCID, 'tbl_chief_cids', 'division_id', $division->id],
            [Role::ChiefOfSGOD, 'tbl_chief_sgods', 'division_id', $division->id],
            [Role::AssistantDivisionSuperintendent, 'tbl_asst_division_superintendents', 'division_id', $division->id],
            [Role::DivisionSuperintendent, 'tbl_division_superintendents', 'division_id', $division->id],
        ];

        foreach ($accounts as [$role, $table, $assignmentColumn, $assignmentId]) {
            $user = $this->userWithRole($role);
            $record = ['user_id' => $user->id, $assignmentColumn => $assignmentId, 'status' => 1, 'email' => $user->username];

            if ($role === Role::DivisionSupervisor) {
                $record['subject_id'] = '';
            }

            DB::table($table)->insert($record);

            $this->actingAs($user)->postJson('/getDistricts')->assertOk()->assertJsonFragment(['name' => $district->name]);
            $this->actingAs($user)->postJson('/getSchools', ['grade_level_id' => 1])->assertOk()->assertJsonFragment(['name' => $school->name]);
            $this->actingAs($user)->postJson('/getTeachers', ['school_id' => $school->id])->assertOk()->assertJsonFragment(['id' => $teacher->id]);
        }
    }

    public function test_reports_screen_contains_an_assessment_date_filter(): void
    {
        [$division] = $this->hierarchy();
        $user = $this->userWithRole(Role::DivisionSupervisor);
        DB::table('tbl_division_supervisors')->insert([
            'user_id' => $user->id,
            'division_id' => $division->id,
            'status' => 1,
            'email' => $user->username,
            'subject_id' => '',
        ]);

        $this->actingAs($user)->get('/reports')
            ->assertOk()
            ->assertSee('Date of Assessment')
            ->assertSee('name="assessment_date"', false);
    }

    public function test_assessment_date_limits_the_assessments_used_by_a_report(): void
    {
        [$division, , $school] = $this->hierarchy();
        $user = $this->userWithRole(Role::DivisionSupervisor);
        DB::table('tbl_division_supervisors')->insert([
            'user_id' => $user->id,
            'division_id' => $division->id,
            'status' => 1,
            'email' => $user->username,
            'subject_id' => '',
        ]);

        $year = new AcademicYear;
        $year->from = '2026';
        $year->to = '2027';
        $year->is_active = 1;
        $year->save();

        $classroom = new Classroom;
        $classroom->section_id = 1;
        $classroom->grade_level_id = 1;
        $classroom->academic_year_id = $year->id;
        $classroom->school_id = $school->id;
        $classroom->save();

        $teacherClass = new TeacherClass;
        $teacherClass->classroom_id = $classroom->id;
        $teacherClass->teacher_id = 1;
        $teacherClass->subject_id = 1;
        $teacherClass->advisory = 1;
        $teacherClass->save();

        foreach (['2026-08-01', '2026-08-15'] as $date) {
            $assessment = new Assessment;
            $assessment->title = $date;
            $assessment->date = $date;
            $assessment->number_of_items = 1;
            $assessment->assessment_type_id = 1;
            $assessment->period_id = 1;
            $assessment->grade_level_id = 1;
            $assessment->subject_id = 1;
            $assessment->teacher_id = 1;
            $assessment->academic_year_id = $year->id;
            $assessment->save();

            $classAssessment = new ClassAssessment;
            $classAssessment->class_id = $teacherClass->id;
            $classAssessment->assessment_id = $assessment->id;
            $classAssessment->save();
        }

        $this->actingAs($user);
        $request = Request::create('/reports/generate', 'POST', [
            'assessment_date' => '2026-08-15',
            'assessment_type_id' => 1,
            'grade_level_id' => 1,
            'subject_id' => 1,
            'period_id' => 1,
        ]);

        $assessments = app(ReportController::class)->getAssessments($request);

        $this->assertCount(1, $assessments);
        $this->assertSame('2026-08-15', $assessments->first()->date);
    }

    /**
     * @return array{Division, District, School}
     */
    private function hierarchy(): array
    {
        $division = new Division;
        $division->name = 'North Division';
        $division->save();

        $district = new District;
        $district->name = 'North District';
        $district->division_id = $division->id;
        $district->save();

        $school = new School;
        $school->code = 'NORTH-001';
        $school->name = 'North School';
        $school->address = 'North';
        $school->school_category_id = 1;
        $school->school_type_id = 1;
        $school->district_id = $district->id;
        $school->save();

        return [$division, $district, $school];
    }
}
