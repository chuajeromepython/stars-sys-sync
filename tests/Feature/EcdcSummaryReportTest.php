<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Jobs\GenerateEcdcSummaryReport;
use App\Services\EcdcSummaryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class EcdcSummaryReportTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    #[TestWith(['school', 'F9', 'U9', 'AJ9', 'E9'])]
    #[TestWith(['district', 'G8', 'V8', 'AK8', 'F8'])]
    #[TestWith(['division', 'F8', 'U8', 'AJ8', 'E8'])]
    public function test_queued_reports_count_students_and_calculate_age_percentages(string $scope, string $young, string $middle, string $old, string $enrollment): void
    {
        Storage::fake('local');
        Queue::fake([GenerateEcdcSummaryReport::class]);
        [$user, $filters, $classroom] = $this->fixture($scope);
        $this->student($classroom, 'M', '2021-05-01', true);
        $this->student($classroom, 'F', '2021-05-01', true);
        $this->student($classroom, 'M', '2020-10-01', true);
        $this->student($classroom, 'M', '2020-05-01', true);
        $this->student($classroom, 'F', '2021-05-01', false);

        $response = $this->actingAs($user)->postJson('/reports/ecdc', $filters)->assertAccepted()->assertJsonPath('status', 'queued');
        $id = $response->json('id');
        Queue::assertPushed(GenerateEcdcSummaryReport::class, 1);
        (new GenerateEcdcSummaryReport($id))->handle(app(EcdcSummaryReport::class));

        $this->getJson($response->json('status_url'))->assertOk()->assertJsonPath('status', 'ready');
        $state = Cache::get('ecdc-summary:'.$id);
        $book = IOFactory::load(Storage::disk('local')->path($state['path']));
        $sheet = $book->getSheet(0);
        $this->assertSame(1, $sheet->getCell($young)->getValue());
        $this->assertSame(1, $sheet->getCell($middle)->getValue());
        $this->assertSame(1, $sheet->getCell($old)->getValue());
        $this->assertSame(5, $sheet->getCell($enrollment)->getValue());
        $ages = $book->getSheetByName('Interpretation by age');
        $this->assertSame(2, $ages->getCell('G2')->getValue());
        $this->assertSame(1, $ages->getCell('H2')->getValue());
        $this->assertSame(1, $ages->getCell('D2')->getValue());
        $this->assertSame(1, $ages->getCell('E2')->getValue());
        $this->get('/reports/ecdc/'.$id.'/download')->assertOk()->assertDownload();
        $this->postJson('/reports/ecdc', $filters)->assertOk()->assertJsonPath('id', $id);
        Queue::assertPushed(GenerateEcdcSummaryReport::class, 1);
        $book->disconnectWorksheets();
    }

    public function test_pending_requests_are_deduplicated_and_rate_limited(): void
    {
        Queue::fake([GenerateEcdcSummaryReport::class]);
        [$user, $filters] = $this->fixture();
        $first = $this->actingAs($user)->postJson('/reports/ecdc', $filters)->assertAccepted();
        $this->postJson('/reports/ecdc', $filters)->assertOk()->assertJsonPath('id', $first->json('id'));
        $this->postJson('/reports/ecdc', $filters)->assertOk();
        $this->postJson('/reports/ecdc', $filters)->assertTooManyRequests();
        Queue::assertPushed(GenerateEcdcSummaryReport::class, 1);
    }

    public function test_interpretation_percentage_uses_its_age_group_and_not_total_enrollment(): void
    {
        Storage::fake('local');
        [, $filters, $classroom] = $this->fixture();
        $this->student($classroom, 'M', '2021-05-01', true);
        $advanced = $this->student($classroom, 'F', '2021-05-01', false);
        $this->student($classroom, 'F', '2021-05-01', false);
        $lengths = [1 => 13, 2 => 11, 3 => 27, 4 => 5, 5 => 8, 6 => 21, 7 => 24];
        foreach ($lengths as $domain => $length) {
            if ($domain !== 1) {
                DB::table('tbl_ecdc_domains')->insert(['id' => $domain, 'domain' => 'Domain '.$domain]);
            }
            for ($i = 0; $i < $length; $i++) {
                $competency = DB::table('tbl_ecdc_competencies')->insertGetId(['domain_id' => $domain, 'competency' => 'Competency '.$i]);
                DB::table('tbl_student_ecdcs')->insert(['student_id' => $advanced, 'ecdc_id' => 1, 'ecdc_competency_id' => $competency, 'score' => '1', 'date_of_assessment' => '2026-05-01']);
            }
        }

        $path = app(EcdcSummaryReport::class)->generate('mixed-interpretations', $filters);

        $book = IOFactory::load(Storage::disk('local')->path($path));
        $ages = $book->getSheetByName('Interpretation by age');
        $this->assertSame(0.5, $ages->getCell('H2')->getValue());
        $this->assertSame(0.5, $ages->getCell('H5')->getValue());
        $this->assertSame(2, $ages->getCell('G2')->getValue());
        $this->assertSame(1, $book->getSheet(0)->getCell('P9')->getValue());
        $this->assertEqualsWithDelta(1 / 3, $book->getSheet(0)->getCell('BP9')->getValue(), 0.00001);
        $this->assertSame(0, $ages->getCell('H7')->getValue());
        $book->disconnectWorksheets();
    }

    #[TestWith(['2022-05-01', null])]
    #[TestWith(['2022-04-01', 'F9'])]
    #[TestWith(['2021-05-01', 'F9'])]
    #[TestWith(['2021-04-01', 'U9'])]
    #[TestWith(['2020-06-01', 'U9'])]
    #[TestWith(['2020-05-01', 'AJ9'])]
    #[TestWith(['2019-06-01', 'AJ9'])]
    #[TestWith(['2019-05-01', null])]
    public function test_age_boundaries_use_the_individual_assessment_date(string $birth, ?string $cell): void
    {
        Storage::fake('local');
        [, $filters, $classroom] = $this->fixture();
        $this->student($classroom, 'M', $birth, true);

        $path = app(EcdcSummaryReport::class)->generate('age-boundary', $filters);

        $book = IOFactory::load(Storage::disk('local')->path($path));
        if ($cell === null) {
            $this->assertSame(0, $book->getSheet(0)->getCell('AZ9')->getValue());
            $this->assertSame(1, $book->getSheetByName('Report notes')->getCell('B6')->getValue());
        } else {
            $this->assertSame(1, $book->getSheet(0)->getCell($cell)->getValue());
        }
        $book->disconnectWorksheets();
    }

    public function test_reports_are_private_and_pending_files_cannot_be_downloaded(): void
    {
        Queue::fake([GenerateEcdcSummaryReport::class]);
        [$user, $filters] = $this->fixture();
        $response = $this->actingAs($user)->postJson('/reports/ecdc', $filters)->assertAccepted();
        $id = $response->json('id');
        $this->get('/reports/ecdc/'.$id.'/download')->assertConflict();
        $this->actingAs($this->userWithRole(Role::SystemAdministrator))->getJson('/reports/ecdc/'.$id)->assertNotFound();
        $this->get('/reports/ecdc/'.$id.'/download')->assertNotFound();
    }

    public function test_school_users_cannot_request_district_or_division_reports(): void
    {
        Queue::fake([GenerateEcdcSummaryReport::class]);
        [, $filters] = $this->fixture();
        $user = $this->userWithRole(Role::SchoolHead);
        DB::table('tbl_school_supervisors')->insert(['user_id' => $user->id, 'school_id' => $filters['scope_id'], 'status' => 1, 'email' => $user->username]);
        $this->actingAs($user)->postJson('/reports/ecdc', array_replace($filters, ['scope' => 'district']))->assertNotFound();
        $this->postJson('/reports/ecdc', array_replace($filters, ['scope' => 'division']))->assertNotFound();
        Queue::assertNothingPushed();
    }

    public function test_invalid_filters_and_missing_permission_are_rejected(): void
    {
        Queue::fake([GenerateEcdcSummaryReport::class]);
        [$user] = $this->fixture();
        $this->actingAs($user)->postJson('/reports/ecdc', [])->assertUnprocessable()->assertJsonValidationErrors(['scope', 'scope_id', 'academic_year_id', 'period']);
        $this->actingAs($this->createUser())->postJson('/reports/ecdc', [])->assertForbidden();
        Queue::assertNothingPushed();
    }

    public function test_failed_jobs_expose_safe_messages_and_allow_retry(): void
    {
        Queue::fake([GenerateEcdcSummaryReport::class]);
        [$user, $filters] = $this->fixture();
        $response = $this->actingAs($user)->postJson('/reports/ecdc', $filters)->assertAccepted();
        (new GenerateEcdcSummaryReport($response->json('id')))->failed(new \RuntimeException('private diagnostic'));

        $this->getJson($response->json('status_url'))->assertOk()->assertJsonPath('status', 'failed')->assertDontSee('private diagnostic');
        $retry = $this->postJson('/reports/ecdc', $filters)->assertAccepted();
        $this->assertNotSame($response->json('id'), $retry->json('id'));
    }

    public function test_reports_page_includes_ecdc_generation(): void
    {
        [$user] = $this->fixture();
        $this->actingAs($user)->get('/reports')->assertOk()->assertSee('ECDC consolidated reports')->assertSee('ecdc-summary-form');
    }

    public function test_ecdc_student_card_displays_the_person_name_and_lrn(): void
    {
        [$user, , $classroom] = $this->fixture();
        $student = $this->student($classroom, 'F', '2021-05-01', false);
        $studentUser = DB::table('tbl_students')->where('id', $student)->value('user_id');
        $person = DB::table('tbl_users')->where('id', $studentUser)->value('person_id');
        DB::table('tbl_persons')->where('id', $person)->update(['first_name' => 'Maria', 'middle_name' => 'Reyes', 'last_name' => 'Santos']);
        $lrn = DB::table('tbl_students')->where('id', $student)->value('lrn');

        $this->actingAs($user)->get('/ecdcs/classroom/'.$classroom.'/card/'.$student)
            ->assertOk()->assertSee('Santos, Maria Reyes')->assertSee('LRN: '.$lrn);
    }

    #[TestWith(['School Head', 'tbl_school_supervisors'])]
    #[TestWith(['Teacher', 'tbl_teachers'])]
    #[TestWith(['Department Head', 'tbl_department_heads'])]
    public function test_school_accounts_automatically_use_their_assignment(string $classification, string $table): void
    {
        [, $filters] = $this->fixture();
        $user = $this->userWithRole(Role::fromClassification($classification));
        $assignment = ['user_id' => $user->id, 'school_id' => $filters['scope_id'], 'email' => $user->username];
        if ($classification === 'School Head') {
            $assignment['status'] = 1;
        }
        if ($classification === 'Department Head') {
            $assignment['subject_id'] = 1;
        }
        DB::table($table)->insert($assignment);
        $period = DB::table('tbl_periods')->insertGetId(['period' => 'First']);
        $subject = DB::table('tbl_subjects')->insertGetId(['title' => 'English']);
        $type = DB::table('tbl_assessment_types')->insertGetId(['type' => 'Summative']);
        $grade = DB::table('tbl_grade_levels')->insertGetId(['level' => 'Grade 1']);
        $inputs = ['education_level_id' => 1, 'period_id' => $period, 'grade_level_id' => $grade, 'subject_id' => $subject, 'assessment_type_id' => $type, 'assessment_date' => '2026-05-01', 'report_type_id' => 'LC'];

        $this->actingAs($user)->get('/reports')->assertOk()->assertSee('Assigned school')->assertDontSee('id="school_ids"', false);
        $this->postJson('/reports/generate', $inputs)->assertOk()->assertJsonPath('reports.0.school_id', $filters['scope_id']);
        $this->postJson('/reports/generate', $inputs + ['school_ids' => [999999]])->assertOk()->assertJsonPath('reports.0.school_id', $filters['scope_id']);
    }

    public function test_school_head_ecdc_report_uses_the_assigned_school(): void
    {
        Queue::fake([GenerateEcdcSummaryReport::class]);
        [, $filters] = $this->fixture();
        $user = $this->userWithRole(Role::SchoolHead);
        DB::table('tbl_school_supervisors')->insert(['user_id' => $user->id, 'school_id' => $filters['scope_id'], 'email' => $user->username, 'status' => 1]);
        $filters['scope_id'] = 999999;

        $response = $this->actingAs($user)->postJson('/reports/ecdc', $filters)->assertAccepted();

        $this->assertSame(1, Cache::get('ecdc-summary:'.$response->json('id'))['filters']['scope_id']);
        Queue::assertPushed(GenerateEcdcSummaryReport::class, 1);
    }

    public function test_unassigned_school_account_cannot_generate_a_report(): void
    {
        $user = $this->userWithRole(Role::SchoolHead);

        $this->actingAs($user)->postJson('/reports/generate', ['school_ids' => [1]])->assertForbidden();
    }

    #[TestWith([1, 'Grade 1', 'Grade 7'])]
    #[TestWith([2, 'Grade 10', 'Grade 11'])]
    #[TestWith([3, 'Grade 12', 'Grade 6'])]
    public function test_school_report_grades_match_the_education_level_and_exclude_ecdc(int $education, string $allowed, string $outside): void
    {
        [$user, $filters] = $this->fixture();
        $grade = DB::table('tbl_grade_levels')->insertGetId(['level' => $allowed]);
        $wrongGrade = DB::table('tbl_grade_levels')->insertGetId(['level' => $outside]);
        $period = DB::table('tbl_periods')->insertGetId(['period' => 'First']);
        $subject = DB::table('tbl_subjects')->insertGetId(['title' => 'English']);
        $type = DB::table('tbl_assessment_types')->insertGetId(['type' => 'Summative']);
        $ecd = DB::table('tbl_assessment_types')->insertGetId(['type' => 'ECD']);
        $ecdSubject = DB::table('tbl_subjects')->insertGetId(['title' => 'ECD']);
        $inputs = ['school_ids' => [$filters['scope_id']], 'education_level_id' => $education, 'period_id' => $period, 'grade_level_id' => $grade, 'subject_id' => $subject, 'assessment_type_id' => $type, 'assessment_date' => '2026-05-01', 'report_type_id' => 'LC'];

        $this->actingAs($user)->postJson('/reports/generate', $inputs)->assertOk();
        $this->postJson('/reports/generate', array_replace($inputs, ['grade_level_id' => $wrongGrade]))->assertInvalid('grade_level_id');
        $this->postJson('/reports/generate', array_replace($inputs, ['grade_level_id' => 1]))->assertInvalid('grade_level_id');
        $this->postJson('/reports/generate', array_replace($inputs, ['assessment_type_id' => $ecd]))->assertInvalid('assessment_type_id');
        $this->postJson('/reports/generate', array_replace($inputs, ['subject_id' => $ecdSubject]))->assertInvalid('subject_id');
        $this->get('/reports')->assertViewHas('subjects', fn ($subjects): bool => ! $subjects->contains('id', $ecdSubject));
        $this->get('/reports')->assertOk()->assertViewHas('assessmentTypes', fn ($types): bool => ! $types->contains('id', $ecd))->assertViewHas('gradeLevels', fn ($grades): bool => ! $grades->contains('level', 'Kinder'));
    }

    public function test_school_head_scope_is_fixed_and_teacher_ecdc_tab_is_absent(): void
    {
        [, $filters] = $this->fixture();
        $head = $this->userWithRole(Role::SchoolHead);
        DB::table('tbl_school_supervisors')->insert(['user_id' => $head->id, 'school_id' => $filters['scope_id'], 'email' => $head->username, 'status' => 1]);

        $this->actingAs($head)->get('/reports')->assertOk()->assertSee('type="hidden" id="ecdc-scope" name="scope" value="school"', false)->assertDontSee('<select id="ecdc-scope"', false);
        $teacher = $this->userWithRole(Role::Teacher);
        $this->actingAs($teacher)->get('/reports')->assertOk()->assertDontSee('id="ecdc-reports-tab"', false)->assertDontSee('id="ecdc-summary-form"', false);
    }

    public function test_expired_cached_report_is_regenerated(): void
    {
        Storage::fake('local');
        Queue::fake([GenerateEcdcSummaryReport::class]);
        $this->freezeTime();
        [$user, $filters] = $this->fixture();
        $response = $this->actingAs($user)->postJson('/reports/ecdc', $filters)->assertAccepted();
        (new GenerateEcdcSummaryReport($response->json('id')))->handle(app(EcdcSummaryReport::class));
        $this->travel(11)->minutes();

        $fresh = $this->postJson('/reports/ecdc', $filters)->assertAccepted();

        $this->assertNotSame($response->json('id'), $fresh->json('id'));
        Queue::assertPushed(GenerateEcdcSummaryReport::class, 2);
    }

    public function test_latest_assessment_counts_once_and_other_periods_are_excluded(): void
    {
        Storage::fake('local');
        [, $filters, $classroom] = $this->fixture();
        $student = $this->student($classroom, 'M', '2021-05-01', true);
        $assessment = DB::table('tbl_ecdcs')->first();
        foreach (['1', '3'] as $period) {
            $id = DB::table('tbl_ecdcs')->insertGetId(['classroom_id' => $classroom, 'academic_year_id' => $assessment->academic_year_id, 'period' => $period, 'teacher_id' => 1, 'source' => '2', 'date' => '2026-06-01']);
            DB::table('tbl_student_ecdcs')->insert(['student_id' => $student, 'ecdc_id' => $id, 'ecdc_competency_id' => 1, 'score' => '-', 'date_of_assessment' => '2026-06-01']);
        }

        $path = app(EcdcSummaryReport::class)->generate('latest-assessment', $filters);

        $book = IOFactory::load(Storage::disk('local')->path($path));
        $sheet = $book->getSheet(0);
        $this->assertSame(0, $sheet->getCell('F9')->getValue());
        $this->assertSame(1, $sheet->getCell('U9')->getValue());
        $this->assertSame(1, $sheet->getCell('AZ9')->getValue());
        $book->disconnectWorksheets();
    }

    public function test_pruning_removes_only_expired_generated_report_files(): void
    {
        Storage::fake('local');
        $disk = Storage::disk('local');
        $old = 'ecdc-reports/00000000-0000-4000-8000-000000000001.xlsx';
        $recent = 'ecdc-reports/00000000-0000-4000-8000-000000000002.xlsx';
        $disk->put($old, 'old');
        $disk->put($recent, 'recent');
        $disk->put('ecdc-reports/keep.xlsx', 'unrelated');
        touch($disk->path($old), now()->subDays(3)->timestamp);

        $this->artisan('reports:prune-ecdc')->assertSuccessful();

        $disk->assertMissing($old);
        $disk->assertExists([$recent, 'ecdc-reports/keep.xlsx']);
    }

    private function fixture(string $scope = 'school'): array
    {
        $user = $this->userWithRole(Role::SystemAdministrator);
        $division = DB::table('tbl_divisions')->insertGetId(['name' => 'Test Division']);
        $district = DB::table('tbl_districts')->insertGetId(['name' => 'Test District', 'division_id' => $division]);
        $school = DB::table('tbl_schools')->insertGetId(['name' => 'Test School', 'code' => '001234', 'address' => 'Address', 'school_category_id' => 1, 'school_type_id' => 1, 'district_id' => $district]);
        $year = DB::table('tbl_academic_years')->insertGetId(['from' => '2026', 'to' => '2027', 'is_active' => 1]);
        $grade = DB::table('tbl_grade_levels')->insertGetId(['level' => 'Kinder']);
        $section = DB::table('tbl_sections')->insertGetId(['section' => 'Maple', 'school_id' => $school]);
        $classroom = DB::table('tbl_classrooms')->insertGetId(['school_id' => $school, 'academic_year_id' => $year, 'grade_level_id' => $grade, 'section_id' => $section]);
        DB::table('tbl_ecdcs')->insert(['classroom_id' => $classroom, 'academic_year_id' => $year, 'period' => '1', 'teacher_id' => 1, 'source' => '2', 'date' => '2026-06-01']);
        $domain = DB::table('tbl_ecdc_domains')->insertGetId(['domain' => 'Gross Motor']);
        DB::table('tbl_ecdc_competencies')->insert(['domain_id' => $domain, 'competency' => 'Test competency']);

        return [$user, ['scope' => $scope, 'scope_id' => match ($scope) {
            'school' => $school, 'district' => $district, 'division' => $division
        }, 'academic_year_id' => $year, 'period' => 1], $classroom];
    }

    private function student(int $classroom, string $gender, string $birth, bool $assessed): int
    {
        $user = $this->createUser();
        DB::table('tbl_persons')->where('id', $user->person_id)->update(['gender' => $gender, 'birth_date' => $birth]);
        $student = DB::table('tbl_students')->insertGetId(['user_id' => $user->id, 'lrn' => str_pad((string) $user->id, 12, '0'), 'school_id' => 1, 'email' => $user->username]);
        DB::table('tbl_student_classrooms')->insert(['student_id' => $student, 'classroom_id' => $classroom, 'status' => 1]);
        if ($assessed) {
            DB::table('tbl_student_ecdcs')->insert(['student_id' => $student, 'ecdc_id' => 1, 'ecdc_competency_id' => 1, 'score' => '1', 'date_of_assessment' => '2026-05-01']);
        }

        return $student;
    }
}
