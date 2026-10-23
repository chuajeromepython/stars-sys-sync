<?php

namespace Tests\Feature;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class CompetencyUploadTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_valid_competency_workbook_imports_rows(): void
    {
        DB::table('tbl_subjects')->insert(['title' => 'Mathematics']);
        DB::table('tbl_grade_levels')->insert(['level' => 'Grade 1']);

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->post('/competencies/upload', [
                'file' => $this->workbook([
                    ['MATH-1', 'Add numbers', "Mathematics\u{00A0}", 'Grade 1'],
                ]),
            ])
            ->assertRedirect('/competencies')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tbl_competencies', [
            'code' => 'MATH-1', 'description' => 'Add numbers',
        ]);
    }

    public function test_invalid_row_rolls_back_all_competencies(): void
    {
        DB::table('tbl_subjects')->insert(['title' => 'Mathematics']);
        DB::table('tbl_grade_levels')->insert(['level' => 'Grade 1']);

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->post('/competencies/upload', [
                'file' => $this->workbook([
                    ['MATH-1', 'Add numbers', 'Mathematics', 'Grade 1'],
                    ['MATH-2', 'Subtract numbers', 'Unknown', 'Grade 1'],
                ]),
            ])
            ->assertRedirect('/competencies')
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('tbl_competencies', 0);
    }

    /** @param list<list<string>> $rows */
    private function workbook(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ENCODE here');
        $sheet->fromArray([['Code', 'Description', 'Subject', 'Grade', 'Period', 'Week'], ['Instructions'], ...$rows]);
        $path = tempnam(sys_get_temp_dir(), 'competency-');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);

        return new UploadedFile($path, 'competencies.xlsx', null, null, true);
    }
}
