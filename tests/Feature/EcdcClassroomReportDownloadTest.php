<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EcdcClassroomReportDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_downloads_the_classroom_sf5_k_report_from_eosy_results(): void
    {
        Storage::fake('public');

        $divisionId = DB::table('tbl_divisions')->insertGetId(['name' => 'Test Division']);
        $districtId = DB::table('tbl_districts')->insertGetId([
            'name' => 'Test District',
            'division_id' => $divisionId,
        ]);
        $schoolId = DB::table('tbl_schools')->insertGetId([
            'code' => '001234',
            'name' => 'Test School',
            'address' => 'School Address',
            'school_category_id' => 1,
            'school_type_id' => 1,
            'district_id' => $districtId,
        ]);
        $academicYearId = DB::table('tbl_academic_years')->insertGetId([
            'from' => '2025',
            'to' => '2026',
            'is_active' => 1,
        ]);
        $gradeLevelId = DB::table('tbl_grade_levels')->insertGetId(['level' => 'Kinder']);
        $sectionId = DB::table('tbl_sections')->insertGetId([
            'section' => 'Maple',
            'school_id' => $schoolId,
        ]);
        $classroomId = DB::table('tbl_classrooms')->insertGetId([
            'section_id' => $sectionId,
            'grade_level_id' => $gradeLevelId,
            'academic_year_id' => $academicYearId,
            'school_id' => $schoolId,
        ]);

        $teacherUserId = $this->createUser('Teacher', 'M', 'Teacher');
        $teacherId = DB::table('tbl_teachers')->insertGetId([
            'email' => 'teacher@example.test',
            'user_id' => $teacherUserId,
            'school_id' => $schoolId,
        ]);
        $maleStudentId = $this->createStudent($schoolId, $classroomId, '100000000001', 'Alex', 'Able', 'M');
        $femaleStudentId = $this->createStudent($schoolId, $classroomId, '100000000002', 'Bailey', 'Baker', 'F');
        $missingResultStudentId = $this->createStudent($schoolId, $classroomId, '100000000003', 'Casey', 'Cruz', 'F');

        $ecdcId = DB::table('tbl_ecdcs')->insertGetId([
            'classroom_id' => $classroomId,
            'academic_year_id' => $academicYearId,
            'period' => '3',
            'teacher_id' => $teacherId,
            'source' => '2',
            'date' => '2026-06-01',
        ]);
        DB::table('tbl_student_ecdcs')->insert([
            [
                'student_id' => $maleStudentId,
                'ecdc_id' => $ecdcId,
                'ecdc_competency_id' => 1,
                'score' => 1,
            ],
            [
                'student_id' => $femaleStudentId,
                'ecdc_id' => $ecdcId,
                'ecdc_competency_id' => 1,
                'score' => 1,
            ],
        ]);
        Storage::disk('public')->put('ecdc-'.$ecdcId.'.json', json_encode([
            $maleStudentId => [
                'name' => 'Able, Alex',
                'standard_score' => 130,
                'interpretation' => 'Suggest highly advanced development',
            ],
            $femaleStudentId => [
                'name' => 'Baker, Bailey',
                'standard_score' => 78,
                'interpretation' => 'Suggest slight delay in overall development',
            ],
        ]));

        Permission::findOrCreate('ecdcs.download', 'web');
        $user = User::findOrFail($teacherUserId);
        $user->givePermissionTo('ecdcs.download');

        $response = $this->actingAs($user)->get(route('ecdcs.classroom.report.download', $classroomId));
        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));

        $temporaryFile = tempnam(sys_get_temp_dir(), 'ecdc-sf5-k-');
        try {
            file_put_contents($temporaryFile, $response->streamedContent());
            $spreadsheet = IOFactory::load($temporaryFile);
            $sheet = $spreadsheet->getSheetByName('SF5-K 1st Session');

            $this->assertSame('Test School', $sheet->getCell('D4')->getValue());
            $this->assertSame('001234', $sheet->getCell('D6')->getValue());
            $this->assertSame('Maple', $sheet->getCell('G6')->getValue());
            $this->assertSame('2025-2026', $sheet->getCell('L6')->getValue());
            $this->assertSame('100000000001', $sheet->getCell('B15')->getValue());
            $this->assertSame(130, $sheet->getCell('G15')->getValue());
            $this->assertSame('GRADE ONE READY', $sheet->getCell('I15')->getValue());
            $this->assertSame('100000000002', $sheet->getCell('B59')->getValue());
            $this->assertSame(78, $sheet->getCell('G59')->getValue());
            $this->assertSame('NEEDS FURTHER INTERVENTION', $sheet->getCell('I59')->getValue());
            $this->assertSame('100000000003', $sheet->getCell('B62')->getValue());
            $this->assertNull($sheet->getCell('G62')->getValue());
            $this->assertSame(1, $sheet->getCell('O12')->getValue());
            $this->assertSame(1, $sheet->getCell('R14')->getValue());
            $this->assertSame(1, $sheet->getCell('P25')->getValue());
            $this->assertSame(1, $sheet->getCell('S35')->getValue());
            $this->assertSame(1, $sheet->getCell('P41')->getValue());
            $this->assertSame(2, $sheet->getCell('S41')->getValue());
            $this->assertSame(3, $sheet->getCell('A91')->getValue());
            $spreadsheet->disconnectWorksheets();
        } finally {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }

        $this->assertDatabaseHas('tbl_student_classrooms', [
            'student_id' => $missingResultStudentId,
            'classroom_id' => $classroomId,
        ]);
    }

    private function createUser(string $classification, string $gender, string $lastName): int
    {
        $personId = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Test',
            'middle_name' => '',
            'last_name' => $lastName,
            'suffix' => '',
            'gender' => $gender,
            'birth_date' => '1990-01-01',
        ]);

        return DB::table('tbl_users')->insertGetId([
            'username' => strtolower($lastName).'-'.uniqid().'@example.test',
            'password' => bcrypt('password'),
            'classification' => $classification,
            'status' => 1,
            'person_id' => $personId,
        ]);
    }

    private function createStudent(int $schoolId, int $classroomId, string $lrn, string $firstName, string $lastName, string $gender): int
    {
        $userId = $this->createUser('Student', $gender, $lastName);
        DB::table('tbl_persons')->where('id', DB::table('tbl_users')->where('id', $userId)->value('person_id'))
            ->update(['first_name' => $firstName]);
        $studentId = DB::table('tbl_students')->insertGetId([
            'lrn' => $lrn,
            'user_id' => $userId,
            'school_id' => $schoolId,
            'email' => strtolower($lastName).'@example.test',
        ]);
        DB::table('tbl_student_classrooms')->insert([
            'student_id' => $studentId,
            'classroom_id' => $classroomId,
            'status' => 1,
        ]);

        return $studentId;
    }
}
