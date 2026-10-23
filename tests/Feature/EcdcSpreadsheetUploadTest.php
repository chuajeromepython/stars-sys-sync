<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Services\EcdcSpreadsheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class EcdcSpreadsheetUploadTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_download_contains_current_domains_and_competencies_without_scores(): void
    {
        Storage::fake('local');
        [$user, , $classroom] = $this->fixture();
        $this->student($classroom, 'F', '2021-05-01', false);
        DB::table('tbl_ecdc_competencies')->where('id', 1)->update(['competency' => 'Current competency text']);
        $domain = DB::table('tbl_ecdc_domains')->insertGetId(['domain' => 'Current custom domain']);
        $competency = DB::table('tbl_ecdc_competencies')->insertGetId(['domain_id' => $domain, 'competency' => 'Current custom competency']);

        $response = $this->actingAs($user)->get('/ecdcs/'.$classroom.'/download_template')->assertOk()->assertDownload();

        $path = Storage::disk('local')->path('download.xlsx');
        file_put_contents($path, $response->streamedContent());
        $book = IOFactory::load($path);
        $sheet = $book->getSheetByName('Encode here');
        $this->assertSame('Current competency text', $sheet->getCell('C5')->getValue());
        $this->assertSame('Current custom domain', $sheet->getCell('B6')->getValue());
        $this->assertSame($competency, $sheet->getCell('A7')->getValue());
        $this->assertSame('Current custom competency', $sheet->getCell('C7')->getValue());
        $this->assertSame('P', $sheet->getCell('D4')->getValue());
        $this->assertSame('O', $sheet->getCell('E4')->getValue());
        $this->assertSame('R', $sheet->getCell('F4')->getValue());
        $this->assertContains('D1:F1', $sheet->getMergeCells());
        $this->assertNull($sheet->getCell('D3')->getValue());
        $this->assertNull($sheet->getCell('D5')->getValue());
        $this->assertSame(7, $sheet->getHighestDataRow());
        $book->disconnectWorksheets();
    }

    public function test_upload_saves_individual_dates_statuses_and_presentation_types_and_skips_untested_students(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$user, , $classroom] = $this->fixture();
        $first = $this->student($classroom, 'M', '2021-05-01', false);
        $second = $this->student($classroom, 'F', '2021-05-01', false);
        $untested = $this->student($classroom, 'F', '2021-05-01', false);
        for ($i = 2; $i <= 6; $i++) {
            DB::table('tbl_ecdc_competencies')->insert(['domain_id' => 1, 'competency' => 'Competency '.$i]);
        }
        $book = app(EcdcSpreadsheet::class)->template($classroom);
        $sheet = $book->getActiveSheet();
        $sheet->setCellValue('D3', Date::PHPToExcel(new \DateTimeImmutable('2026-05-01')));
        $sheet->setCellValue('G3', '2026-06-02');
        $sheet->setCellValue('D5', 1);
        $sheet->setCellValue('F6', 1);
        $sheet->setCellValue('E7', 1);
        $sheet->setCellValue('D8', 1);
        $sheet->setCellValue('D10', 0);
        $sheet->setCellValue('G5', 1);
        $sheet->setCellValue('J5', '-');
        $upload = $this->uploadFile($book);

        $this->actingAs($user)->post('/ecdcs/upload', ['file' => $upload, 'classroom_id' => $classroom, 'period' => 1])->assertRedirect()->assertSessionHasNoErrors();

        foreach ([1 => 'P', 2 => 'R', 3 => 'O', 4 => 'P'] as $competency => $type) {
            $this->assertDatabaseHas('tbl_student_ecdcs', ['student_id' => $first, 'ecdc_competency_id' => $competency, 'score' => '1', 'present_type' => $type, 'date_of_assessment' => '2026-05-01']);
        }
        $this->assertDatabaseHas('tbl_student_ecdcs', ['student_id' => $first, 'ecdc_competency_id' => 5, 'score' => '-', 'present_type' => null]);
        $this->assertDatabaseHas('tbl_student_ecdcs', ['student_id' => $first, 'ecdc_competency_id' => 6, 'score' => '-']);
        $this->assertDatabaseHas('tbl_student_ecdcs', ['student_id' => $second, 'date_of_assessment' => '2026-06-02']);
        $this->assertDatabaseMissing('tbl_student_ecdcs', ['student_id' => $untested]);
        $results = json_decode(Storage::disk('public')->get('ecdc-1.json'), true);
        $this->assertSame('2026-05-01', $results[$first]['date_tested']);
        $this->assertSame('2026-06-02', $results[$second]['date_tested']);
        $this->actingAs($user)->post('/ecdcs/upload', ['file' => $upload, 'classroom_id' => $classroom, 'period' => 1])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseCount('tbl_ecdcs', 1);
        $this->assertDatabaseCount('tbl_student_ecdcs', 12);
    }

    public function test_blank_workbook_does_not_create_assessments(): void
    {
        Storage::fake('local');
        [$user, , $classroom] = $this->fixture();
        $this->student($classroom, 'M', '2021-05-01', false);
        $upload = $this->uploadFile(app(EcdcSpreadsheet::class)->template($classroom));

        $this->actingAs($user)->post('/ecdcs/upload', ['file' => $upload, 'classroom_id' => $classroom, 'period' => 1])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tbl_ecdcs', 0);
        $this->assertDatabaseCount('tbl_student_ecdcs', 0);
    }

    public function test_tested_student_requires_a_valid_individual_date(): void
    {
        Storage::fake('local');
        [$user, , $classroom] = $this->fixture();
        $this->student($classroom, 'M', '2021-05-01', false);
        $book = app(EcdcSpreadsheet::class)->template($classroom);
        $book->getActiveSheet()->setCellValue('D5', 1);

        $this->actingAs($user)->post('/ecdcs/upload', ['file' => $this->uploadFile($book), 'classroom_id' => $classroom, 'period' => 1, 'date' => '2026-01-01'])->assertRedirect()->assertSessionHasErrors('file');

        $this->assertDatabaseCount('tbl_ecdcs', 0);
        $this->assertDatabaseCount('tbl_student_ecdcs', 0);
    }

    public function test_upload_reads_competencies_beyond_the_old_fixed_row_limit(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$user, , $classroom] = $this->fixture();
        $student = $this->student($classroom, 'M', '2021-05-01', false);
        for ($i = 2; $i <= 130; $i++) {
            DB::table('tbl_ecdc_competencies')->insert(['domain_id' => 1, 'competency' => 'Competency '.$i]);
        }
        $book = app(EcdcSpreadsheet::class)->template($classroom);
        $book->getActiveSheet()->setCellValue('D3', '2026-05-01');
        $book->getActiveSheet()->setCellValue('D134', 1);

        $this->actingAs($user)->post('/ecdcs/upload', ['file' => $this->uploadFile($book), 'classroom_id' => $classroom, 'period' => 1])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tbl_student_ecdcs', ['student_id' => $student, 'ecdc_competency_id' => 130, 'score' => '1']);
        $this->assertDatabaseCount('tbl_student_ecdcs', 130);
    }

    private function uploadFile(Spreadsheet $book): UploadedFile
    {
        $path = Storage::disk('local')->path('upload.xlsx');
        IOFactory::createWriter($book, 'Xlsx')->save($path);
        $book->disconnectWorksheets();

        return new UploadedFile($path, 'ecdc-upload.xlsx', null, null, true);
    }

    #[TestWith(['D5', 'X'])]
    #[TestWith(['E5', 1])]
    #[TestWith(['A5', 999999])]
    #[TestWith(['D3', '2026-02-31'])]
    public function test_invalid_spreadsheet_values_are_rejected_before_saving(string $cell, mixed $value): void
    {
        Storage::fake('local');
        [$user, , $classroom] = $this->fixture();
        $this->student($classroom, 'M', '2021-05-01', false);
        $book = app(EcdcSpreadsheet::class)->template($classroom);
        $book->getActiveSheet()->setCellValue('D3', '2026-05-01');
        $book->getActiveSheet()->setCellValue('D5', 1);
        $book->getActiveSheet()->setCellValue($cell, $value);

        $this->actingAs($user)->post('/ecdcs/upload', ['file' => $this->uploadFile($book), 'classroom_id' => $classroom, 'period' => 1])->assertRedirect()->assertSessionHasErrors('file');

        $this->assertDatabaseCount('tbl_ecdcs', 0);
        $this->assertDatabaseCount('tbl_student_ecdcs', 0);
    }

    public function test_upload_collects_all_student_errors_and_saves_nothing(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$user, , $classroom] = $this->fixture();
        $this->student($classroom, 'M', '2021-05-01', false);
        $this->student($classroom, 'F', '2021-05-01', false);
        $this->student($classroom, 'M', '2021-05-01', false);
        $book = app(EcdcSpreadsheet::class)->template($classroom);
        $sheet = $book->getActiveSheet();
        $sheet->setCellValue('D3', '2026-02-31');
        $sheet->setCellValue('E5', 1);
        $sheet->setCellValue('F5', 1);
        $sheet->setCellValue('G3', 'wrong-date');
        $sheet->setCellValue('H5', 1);
        $sheet->setCellValue('I5', 1);
        $sheet->setCellValue('J3', '2026-05-01');
        $sheet->setCellValue('J5', 1);

        $response = $this->actingAs($user)->post('/ecdcs/upload', ['file' => $this->uploadFile($book), 'classroom_id' => $classroom, 'period' => 1]);

        $response->assertRedirect()->assertSessionHasErrors('file');
        $errors = session('errors')->get('file');
        $this->assertCount(4, $errors);
        foreach (['D3', 'G3', 'D5:F5', 'G5:I5'] as $location) {
            $this->assertStringContainsString($location, implode(' ', $errors));
        }
        $this->assertDatabaseCount('tbl_ecdcs', 0);
        $this->assertDatabaseCount('tbl_student_ecdcs', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    #[TestWith([null])]
    #[TestWith(['Mid of School Year'])]
    #[TestWith(['BOSY'])]
    public function test_invalid_c2_is_rejected_before_student_and_competency_validation(?string $period): void
    {
        Storage::fake('local');
        [$user, , $classroom] = $this->fixture();
        $this->student($classroom, 'M', '2021-05-01', false);
        $book = app(EcdcSpreadsheet::class)->template($classroom);
        $book->getActiveSheet()->setCellValue('C2', $period);
        $book->getActiveSheet()->setCellValue('A5', 999999);
        $book->getActiveSheet()->setCellValue('D3', 'invalid date');
        $book->getActiveSheet()->setCellValue('D5', 'invalid mark');

        $this->actingAs($user)->post('/ecdcs/upload', ['file' => $this->uploadFile($book), 'classroom_id' => $classroom, 'period' => 1])
            ->assertRedirect()->assertSessionHasErrors('file');

        $this->assertSame(['C2 must contain Beginning of School Year or Ending of School Year.'], session('errors')->get('file'));
        $this->assertDatabaseCount('tbl_ecdcs', 0);
        $this->assertDatabaseCount('tbl_student_ecdcs', 0);
    }

    #[TestWith(['Beginning of School Year'])]
    #[TestWith(['Ending of School Year'])]
    public function test_supported_c2_values_allow_student_validation(string $period): void
    {
        Storage::fake('local');
        [$user, , $classroom] = $this->fixture();
        $this->student($classroom, 'M', '2021-05-01', false);
        $book = app(EcdcSpreadsheet::class)->template($classroom);
        $book->getActiveSheet()->setCellValue('C2', $period);

        $this->actingAs($user)->post('/ecdcs/upload', ['file' => $this->uploadFile($book), 'classroom_id' => $classroom, 'period' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();
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
        DB::table('tbl_teachers')->insert(['user_id' => $user->id, 'school_id' => $school, 'email' => $user->username]);
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
