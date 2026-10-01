<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\CustomFunction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AnswerKeyUploaderTitleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $academicYearId = DB::table('tbl_academic_years')->insertGetId([
            'from' => '2026',
            'to' => '2027',
            'is_active' => 1,
        ]);

        $gradeLevelId = DB::table('tbl_grade_levels')->insertGetId([
            'level' => 'Grade 9',
        ]);

        $subjectId = DB::table('tbl_subjects')->insertGetId([
            'title' => 'Science',
        ]);

        $schoolId = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCI-001',
            'name' => 'Science High School',
            'address' => 'School Address',
            'school_category_id' => 1,
            'school_type_id' => 1,
            'district_id' => 1,
        ]);

        $sectionId = DB::table('tbl_sections')->insertGetId([
            'section' => 'St. Raphael',
            'school_id' => $schoolId,
        ]);

        $classroomId = DB::table('tbl_classrooms')->insertGetId([
            'section_id' => $sectionId,
            'grade_level_id' => $gradeLevelId,
            'academic_year_id' => $academicYearId,
            'school_id' => $schoolId,
        ]);

        DB::table('tbl_periods')->insert([
            'period' => 'First',
        ]);

        DB::table('tbl_assessment_types')->insert([
            ['type' => 'Summative'],
            ['type' => 'Term Exam'],
            ['type' => 'Diagnostic'],
        ]);

        DB::table('tbl_competencies')->insert([
            'code' => 'SCI9-FO-1',
            'description' => 'Identify the parts of a plant.',
            'subject_id' => $subjectId,
            'grade_level_id' => $gradeLevelId,
        ]);

        $personId = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Teacher',
            'middle_name' => 'T',
            'last_name' => 'Uploader',
            'gender' => 'M',
            'birth_date' => '1990-01-01',
        ]);

        $teacherUserId = DB::table('tbl_users')->insertGetId([
            'username' => 'answer.key.uploader@example.test',
            'password' => bcrypt('password'),
            'classification' => 'Teacher',
            'status' => 1,
            'person_id' => $personId,
        ]);

        $teacherId = DB::table('tbl_teachers')->insertGetId([
            'email' => 'uploader@example.test',
            'user_id' => $teacherUserId,
            'school_id' => $schoolId,
        ]);

        DB::table('tbl_teacher_classes')->insert([
            'classroom_id' => $classroomId,
            'teacher_id' => $teacherId,
            'subject_id' => $subjectId,
            'advisory' => 0,
        ]);

        $this->actingAs(User::find($teacherUserId));
    }

    public static function assessmentTypeProvider(): array
    {
        return [
            'term exam' => ['Term Exam', 'First Term Exam in Science 9'],
            'summative' => ['Summative', 'First Summative in Science 9'],
            'diagnostic' => ['Diagnostic', 'First Diagnostic in Science 9'],
        ];
    }

    #[DataProvider('assessmentTypeProvider')]
    public function test_it_composes_the_title_from_the_period_type_subject_and_grade(string $type, string $expectedTitle): void
    {
        $answerKeys = CustomFunction::verifyAnswerKeys($this->answerKeySpreadsheet($type));

        $this->assertArrayHasKey('title', $answerKeys);
        $this->assertSame($expectedTitle, $answerKeys['title']);
    }

    public function test_it_saves_the_composed_title_with_the_answer_keys(): void
    {
        $answerKeys = CustomFunction::verifyAnswerKeys($this->answerKeySpreadsheet('Term Exam'));

        $assessment = Assessment::saveAnswerKeys($answerKeys);

        $this->assertSame('First Term Exam in Science 9', $assessment->title);
        $this->assertDatabaseHas('tbl_assessments', [
            'title' => 'First Term Exam in Science 9',
        ]);
    }

    public function test_it_returns_errors_without_a_title_when_the_uploaded_details_are_invalid(): void
    {
        $answerKeys = CustomFunction::verifyAnswerKeys($this->answerKeySpreadsheet('Periodical'));

        $this->assertArrayNotHasKey('title', $answerKeys);
        $this->assertContains('Invalid Assessment Type.', $answerKeys);
    }

    public function test_compose_assessment_title_builds_the_title_from_its_parts(): void
    {
        $this->assertSame('First Term Exam in Science 9', CustomFunction::composeAssessmentTitle('First', 'Term Exam', 'Science', 'Grade 9'));
        $this->assertSame('First Term Exam in Science 10', CustomFunction::composeAssessmentTitle(' First ', ' Term Exam ', ' Science ', 'Grade 10'));
        $this->assertSame('Term Exam in Science 9', CustomFunction::composeAssessmentTitle(null, 'Term Exam', 'Science', 'Grade 9'));
        $this->assertSame('First Term Exam in Science', CustomFunction::composeAssessmentTitle('First', 'Term Exam', 'Science', null));
    }

    /**
     * Build an answer key upload laid out like the ANSWER-KEY-UPLOADER template.
     *
     * The C1 title cell still holds a teacher typed title to prove the uploader
     * ignores it in favour of the composed title.
     */
    private function answerKeySpreadsheet(string $type): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('C1', 'Title typed by the teacher');
        $sheet->setCellValue('C2', 'Science');
        $sheet->setCellValue('C3', '2026-09-30');
        $sheet->setCellValue('C4', $type);
        $sheet->setCellValue('C5', 2);
        $sheet->setCellValue('E3', 'St. Raphael');
        $sheet->setCellValue('E4', 'Grade 9');
        $sheet->setCellValue('E5', 'First');

        foreach ([1, 2] as $index => $itemNumber) {
            $row = 9 + $index;

            $sheet->setCellValue('A'.$row, $itemNumber);
            $sheet->setCellValue('B'.$row, 'A');
            $sheet->setCellValue('C'.$row, 'SCI9-FO-1');
            $sheet->setCellValue('D'.$row, 'Question '.$itemNumber);
            $sheet->setCellValue('F'.$row, 'Choice A');
            $sheet->setCellValue('G'.$row, 'Choice B');
            $sheet->setCellValue('H'.$row, 'Choice C');
            $sheet->setCellValue('I'.$row, 'Choice D');
        }

        return $spreadsheet;
    }
}
