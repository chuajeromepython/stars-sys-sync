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
        DB::table('tbl_teacher_classes')->insert([
            'classroom_id' => $classroomId,
            'teacher_id' => $teacherId,
            'subject_id' => 1,
            'advisory' => 1,
        ]);
        $schoolHeadUserId = $this->createUser('School Head', 'M', 'Head');
        DB::table('tbl_school_supervisors')->insert([
            'status' => 1,
            'user_id' => $schoolHeadUserId,
            'school_id' => $schoolId,
            'email' => 'head@example.test',
        ]);
        $districtSupervisorUserId = $this->createUser('District Supervisor', 'M', 'District');
        DB::table('tbl_district_supervisors')->insert([
            'status' => 1,
            'user_id' => $districtSupervisorUserId,
            'district_id' => $districtId,
            'email' => 'district@example.test',
        ]);
        $divisionSupervisorUserId = $this->createUser('Division Supervisor', 'M', 'Division');
        DB::table('tbl_division_supervisors')->insert([
            'status' => 1,
            'user_id' => $divisionSupervisorUserId,
            'division_id' => $divisionId,
            'email' => 'division@example.test',
            'subject_id' => '',
        ]);
        $maleStudentId = $this->createStudent($schoolId, $classroomId, '100000000001', 'Alex', 'Able', 'M');
        $secondMaleStudentId = $this->createStudent($schoolId, $classroomId, '100000000004', 'Drew', 'Dover', 'M');
        $thirdMaleStudentId = $this->createStudent($schoolId, $classroomId, '100000000005', 'Eli', 'Ellis', 'M');
        $femaleStudentId = $this->createStudent($schoolId, $classroomId, '100000000002', 'Bailey', 'Baker', 'F');
        $missingResultStudentId = $this->createStudent($schoolId, $classroomId, '100000000003', 'Casey', 'Cruz', 'F');
        $secondFemaleStudentId = $this->createStudent($schoolId, $classroomId, '100000000006', 'Dana', 'Evans', 'F');
        $domainId = DB::table('tbl_ecdc_domains')->insertGetId(['domain' => 'Test domain']);
        $competencyId = DB::table('tbl_ecdc_competencies')->insertGetId([
            'domain_id' => $domainId,
            'competency' => 'Test competency',
        ]);

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
                'ecdc_competency_id' => $competencyId,
                'score' => 1,
            ],
            [
                'student_id' => $secondMaleStudentId,
                'ecdc_id' => $ecdcId,
                'ecdc_competency_id' => $competencyId,
                'score' => 1,
            ],
            [
                'student_id' => $thirdMaleStudentId,
                'ecdc_id' => $ecdcId,
                'ecdc_competency_id' => $competencyId,
                'score' => 1,
            ],
            [
                'student_id' => $femaleStudentId,
                'ecdc_id' => $ecdcId,
                'ecdc_competency_id' => $competencyId,
                'score' => 1,
            ],
            [
                'student_id' => $secondFemaleStudentId,
                'ecdc_id' => $ecdcId,
                'ecdc_competency_id' => $competencyId,
                'score' => 1,
            ],
        ]);
        Storage::disk('public')->put('ecdc-'.$ecdcId.'.json', json_encode([
            $maleStudentId => [
                'name' => 'Able, Alex',
                'standard_score' => 130,
                'interpretation' => 'Average development',
            ],
            $secondMaleStudentId => [
                'name' => 'Dover, Drew',
                'standard_score' => 110,
                'interpretation' => 'Suggest slight delay in overall development',
            ],
            $thirdMaleStudentId => [
                'name' => 'Ellis, Eli',
                'standard_score' => 76,
                'interpretation' => 'Suggest significant delay in overall development',
            ],
            $femaleStudentId => [
                'name' => 'Baker, Bailey',
                'standard_score' => 78,
                'interpretation' => 'Suggest slight delay in overall development - S.S.I.D.O.D. (70-79)',
            ],

        ]));

        $bosyEcdcId = DB::table('tbl_ecdcs')->insertGetId([
            'classroom_id' => $classroomId,
            'academic_year_id' => $academicYearId,
            'period' => '1',
            'teacher_id' => $teacherId,
            'source' => '2',
            'date' => '2025-12-01',
        ]);
        DB::table('tbl_student_ecdcs')->insert([
            'student_id' => $maleStudentId,
            'ecdc_id' => $bosyEcdcId,
            'ecdc_competency_id' => $competencyId,
            'score' => 1,
        ]);
        Storage::disk('public')->put('ecdc-'.$bosyEcdcId.'.json', json_encode([
            $maleStudentId => [
                'name' => 'Able, Alex',
                'standard_score' => 95,
                'interpretation' => 'Average development',
            ],
        ]));

        Permission::findOrCreate('ecdcs.download', 'web');
        $user = User::findOrFail($teacherUserId);
        $user->givePermissionTo('ecdcs.download');

        $response = $this->actingAs($user)->get(route('ecdcs.classroom.report.download', $classroomId));
        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('EOSY SF5-K', $response->headers->get('content-disposition'));

        $temporaryFile = tempnam(sys_get_temp_dir(), 'ecdc-sf5-k-');
        try {
            file_put_contents($temporaryFile, $response->streamedContent());
            $spreadsheet = IOFactory::load($temporaryFile);
            $sheet = $spreadsheet->getSheetByName('SF5-K 1st Session');

            $this->assertSame('Test School', $sheet->getCell('D4')->getValue());
            $this->assertSame('District', $sheet->getCell('G4')->getValue());
            $this->assertSame('Test District', $sheet->getCell('H4')->getValue());
            $this->assertSame('Division', $sheet->getCell('J4')->getValue());
            $this->assertSame('Test Division', $sheet->getCell('K4')->getValue());
            $this->assertSame('Region', $sheet->getCell('S4')->getValue());
            $this->assertSame('Region IV – A CALABARZON', $sheet->getCell('T4')->getValue());
            $this->assertSame('TEACHER, TEST', $sheet->getCell('N63')->getValue());
            $this->assertSame('HEAD, TEST', $sheet->getCell('N69')->getValue());
            $this->assertSame('DISTRICT, TEST', $sheet->getCell('N73')->getValue());
            $this->assertSame('DIVISION, TEST', $sheet->getCell('N77')->getValue());
            $this->assertSame('001234', $sheet->getCell('D6')->getValue());
            $this->assertSame('Maple', $sheet->getCell('G6')->getValue());
            $this->assertSame('2025-2026', $sheet->getCell('L6')->getValue());
            $this->assertSame('100000000001', $sheet->getCell('B15')->getValue());
            $this->assertSame(130, $sheet->getCell('G15')->getValue());
            $this->assertSame('Average development', $sheet->getCell('H15')->getValue());
            $this->assertSame('GRADE ONE READY', $sheet->getCell('I15')->getValue());
            $this->assertSame(110, $sheet->getCell('G17')->getValue());
            $this->assertSame('Suggest slight delay in overall development', $sheet->getCell('H17')->getValue());
            $this->assertSame(76, $sheet->getCell('G18')->getValue());
            $this->assertSame('Suggest significant delay in overall development', $sheet->getCell('H18')->getValue());
            $this->assertSame('100000000002', $sheet->getCell('B59')->getValue());
            $this->assertSame(78, $sheet->getCell('G59')->getValue());
            $this->assertSame('Suggest slight delay in overall development - S.S.I.D.O.D. (70-79)', $sheet->getCell('H59')->getValue());
            $this->assertSame('NEEDS FURTHER INTERVENTION', $sheet->getCell('I59')->getValue());
            $this->assertSame('100000000003', $sheet->getCell('B62')->getValue());
            $this->assertNull($sheet->getCell('G62')->getValue());
            $this->assertSame(29, $sheet->getCell('G63')->getValue());
            $this->assertSame('Suggest significant delay in overall development', $sheet->getCell('H63')->getValue());
            $this->assertSame(2, $sheet->getCell('O12')->getValue());
            $this->assertSame(0, $sheet->getCell('R12')->getValue());
            $this->assertSame(2, $sheet->getCell('T12')->getValue());
            $this->assertSame(1, $sheet->getCell('O14')->getValue());
            $this->assertSame(2, $sheet->getCell('R14')->getValue());
            $this->assertSame(3, $sheet->getCell('T14')->getValue());
            $this->assertSame(0, $sheet->getCell('P25')->getValue());
            $this->assertSame(0, $sheet->getCell('S25')->getValue());
            $this->assertSame(0, $sheet->getCell('S28')->getValue());
            $this->assertSame(1, $sheet->getCell('S35')->getValue());
            $this->assertSame(2, $sheet->getCell('T35')->getValue());
            $this->assertSame(1, $sheet->getCell('P31')->getValue());
            $this->assertSame(1, $sheet->getCell('P35')->getValue());
            $this->assertSame(1, $sheet->getCell('P38')->getValue());
            $this->assertSame(0, $sheet->getCell('T25')->getValue());
            $this->assertSame(0, $sheet->getCell('T28')->getValue());
            $this->assertSame(1, $sheet->getCell('T31')->getValue());
            $this->assertSame(1, $sheet->getCell('S38')->getValue());
            $this->assertSame(2, $sheet->getCell('T38')->getValue());
            $this->assertSame(3, $sheet->getCell('P41')->getValue());
            $this->assertSame(2, $sheet->getCell('S41')->getValue());
            $this->assertSame(6, $sheet->getCell('A91')->getValue());
            $spreadsheet->disconnectWorksheets();
        } finally {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }

        $bosyResponse = $this->actingAs($user)->get(route('ecdcs.classroom.report.download', [
            'classroom' => $classroomId,
            'period' => 1,
        ]));
        $bosyResponse->assertOk();
        $this->assertStringContainsString('BOSY SF5-K', $bosyResponse->headers->get('content-disposition'));

        $bosyTemporaryFile = tempnam(sys_get_temp_dir(), 'ecdc-sf5-k-bosy-');
        try {
            file_put_contents($bosyTemporaryFile, $bosyResponse->streamedContent());
            $bosySpreadsheet = IOFactory::load($bosyTemporaryFile);
            $bosySheet = $bosySpreadsheet->getSheetByName('SF5-K 1st Session');

            $this->assertSame(95, $bosySheet->getCell('G15')->getValue());
            $this->assertSame('Average development', $bosySheet->getCell('H15')->getValue());
            $bosySpreadsheet->disconnectWorksheets();
        } finally {
            if (is_file($bosyTemporaryFile)) {
                unlink($bosyTemporaryFile);
            }
        }

        $this->actingAs($user)->get(route('ecdcs.classroom.report.download', [
            'classroom' => $classroomId,
            'period' => 2,
        ]))->assertNotFound();

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
