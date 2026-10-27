<?php

namespace Tests\Feature;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class AssessmentResultUploadTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_result_header_uses_assessment_subject_and_assigned_subject_teacher(): void
    {
        $viewer = $this->userWithRole(Role::DivisionAdministrator);
        $adviser = $this->userWithRole(Role::Teacher);
        $author = $this->userWithRole(Role::Teacher);
        $subjectTeacher = $this->userWithRole(Role::Teacher);
        DB::table('tbl_persons')->where('id', $adviser->person_id)->update(['last_name' => 'Adviser']);
        DB::table('tbl_persons')->where('id', $author->person_id)->update(['last_name' => 'Author']);
        DB::table('tbl_persons')->where('id', $subjectTeacher->person_id)->update(['last_name' => 'SubjectTeacher']);

        $adviserId = DB::table('tbl_teachers')->insertGetId([
            'email' => $adviser->username, 'user_id' => $adviser->id, 'school_id' => 1,
        ]);
        $authorId = DB::table('tbl_teachers')->insertGetId([
            'email' => $author->username, 'user_id' => $author->id, 'school_id' => 1,
        ]);
        $subjectTeacherId = DB::table('tbl_teachers')->insertGetId([
            'email' => $subjectTeacher->username, 'user_id' => $subjectTeacher->id, 'school_id' => 1,
        ]);
        $yearId = DB::table('tbl_academic_years')->insertGetId(['from' => '2026', 'to' => '2027', 'is_active' => 1]);
        $gradeId = DB::table('tbl_grade_levels')->insertGetId(['level' => 'Grade 1']);
        $periodId = DB::table('tbl_periods')->insertGetId(['period' => 'First']);
        $typeId = DB::table('tbl_assessment_types')->insertGetId(['type' => 'Term Exam']);
        $mathId = DB::table('tbl_subjects')->insertGetId(['title' => 'Mathematics']);
        $englishId = DB::table('tbl_subjects')->insertGetId(['title' => 'English']);
        $sectionId = DB::table('tbl_sections')->insertGetId(['section' => 'A', 'school_id' => 1]);
        $classroomId = DB::table('tbl_classrooms')->insertGetId([
            'section_id' => $sectionId, 'grade_level_id' => $gradeId,
            'academic_year_id' => $yearId, 'school_id' => 1,
        ]);
        $classId = DB::table('tbl_teacher_classes')->insertGetId([
            'classroom_id' => $classroomId, 'teacher_id' => $adviserId,
            'subject_id' => $englishId, 'advisory' => 1,
        ]);
        DB::table('tbl_teacher_classes')->insert([
            'classroom_id' => $classroomId, 'teacher_id' => $subjectTeacherId,
            'subject_id' => $mathId, 'advisory' => 0,
        ]);
        $assessmentId = DB::table('tbl_assessments')->insertGetId([
            'title' => 'Math Test', 'date' => '2026-10-22', 'number_of_items' => 1,
            'assessment_type_id' => $typeId, 'period_id' => $periodId,
            'grade_level_id' => $gradeId, 'subject_id' => $mathId,
            'teacher_id' => $authorId, 'academic_year_id' => $yearId,
        ]);
        $classAssessmentId = DB::table('tbl_class_assessments')->insertGetId([
            'class_id' => $classId, 'assessment_id' => $assessmentId,
        ]);

        $this->actingAs($viewer)->get('/class_assessments/'.$classAssessmentId.'/results')
            ->assertOk()
            ->assertSee('Mathematics')
            ->assertSee('SubjectTeacher')
            ->assertDontSee('Adviser')
            ->assertDontSee('Author')
            ->assertDontSee('English');
    }

    public function test_reupload_replaces_scores_and_refreshes_results_for_all_students(): void
    {
        Storage::fake('public');

        $assessmentId = DB::table('tbl_assessments')->insertGetId([
            'title' => 'Mathematics Test', 'date' => '2026-10-22', 'number_of_items' => 1,
            'assessment_type_id' => 1, 'period_id' => 1, 'grade_level_id' => 1,
            'subject_id' => 1, 'teacher_id' => 1, 'academic_year_id' => 1,
        ]);
        $keyId = DB::table('tbl_assessment_keys')->insertGetId([
            'item_number' => 1, 'assessment_id' => $assessmentId, 'question_id' => 1,
        ]);
        $optionId = DB::table('tbl_options')->insertGetId([
            'option' => 'Correct', 'is_correct' => 1, 'question_id' => 1,
        ]);
        DB::table('tbl_assessment_options')->insert([
            'assignment' => 'A', 'option_id' => $optionId, 'assessment_key_id' => $keyId,
        ]);

        $firstStudentId = $this->studentInClass('123456789012');
        $secondStudentId = $this->studentInClass('123456789013');

        $this->postJson('/api/upload/assessment', [
            'assessment_id' => $assessmentId,
            'class_id' => 1,
            'file_assessment' => $this->csv(['123456789012' => 'A']),
        ])->assertOk()->assertJsonPath('data.students_uploaded', 1);

        $classAssessmentId = DB::table('tbl_class_assessments')->value('id');
        $this->assertSame(1, DB::table('tbl_student_scores')->where('student_id', $firstStudentId)->value('score'));
        $this->assertSame(1, json_decode(Storage::disk('public')->get('res-'.$classAssessmentId.'.json'), true)[$firstStudentId]['score']);

        $this->postJson('/api/upload/assessment', [
            'assessment_id' => $assessmentId,
            'class_id' => 1,
            'file_assessment' => $this->csv(['123456789012' => 'B', '123456789013' => 'A']),
        ])->assertOk()->assertJsonPath('data.students_uploaded', 2);

        $results = json_decode(Storage::disk('public')->get('res-'.$classAssessmentId.'.json'), true);
        $this->assertCount(2, $results);
        $this->assertSame(0, $results[$firstStudentId]['score']);
        $this->assertSame('B', $results[$firstStudentId]['answers'][1]['answer']);
        $this->assertSame(1, $results[$secondStudentId]['score']);
        $this->assertSame(0, DB::table('tbl_student_scores')->where('student_id', $firstStudentId)->value('score'));

        $this->postJson('/api/upload/assessment', [
            'assessment_id' => $assessmentId,
            'class_id' => 1,
            'file_assessment' => UploadedFile::fake()->createWithContent('assessment.csv', implode(';', str_split('123456789012'))),
        ])->assertOk();
        $results = json_decode(Storage::disk('public')->get('res-'.$classAssessmentId.'.json'), true);
        $this->assertSame([], $results[$firstStudentId]['answers']);

        $this->actingAs($this->userWithRole(Role::Teacher))
            ->post('/student_answers/upload', [
                'assessment_id' => $assessmentId,
                'class_id' => 1,
                'file_assessment' => $this->csv(['123456789012' => 'A']),
            ])
            ->assertRedirect('/term-exams/'.$assessmentId);

        $results = json_decode(Storage::disk('public')->get('res-'.$classAssessmentId.'.json'), true);
        $this->assertSame(1, $results[$firstStudentId]['score']);
        $this->assertSame(1, $results[$secondStudentId]['score']);
    }

    private function studentInClass(string $lrn): int
    {
        $personId = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Test', 'last_name' => $lrn, 'gender' => 'M', 'birth_date' => '2015-01-01',
        ]);
        $userId = DB::table('tbl_users')->insertGetId([
            'username' => $lrn.'@student.test', 'password' => bcrypt('password'),
            'classification' => 'Student', 'status' => 1, 'person_id' => $personId,
        ]);
        $studentId = DB::table('tbl_students')->insertGetId([
            'lrn' => $lrn, 'email' => $lrn.'@student.test', 'user_id' => $userId, 'school_id' => 1,
        ]);
        DB::table('tbl_student_classes')->insert([
            'student_id' => $studentId, 'class_id' => 1, 'status' => 1,
        ]);

        return $studentId;
    }

    /** @param array<string, string> $answers */
    private function csv(array $answers): UploadedFile
    {
        $rows = [];
        foreach ($answers as $lrn => $answer) {
            $rows[] = implode(';', str_split($lrn)).';'.$answer;
        }

        return UploadedFile::fake()->createWithContent('assessment.csv', implode("\n", $rows));
    }
}
