<?php

namespace Tests\Unit;

use App\Models\ClassAssessment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClassAssessmentScoreAnalysisTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tbl_assessments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('number_of_items');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tbl_class_assessments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_score_analysis_handles_single_student_without_division_by_zero(): void
    {
        $assessmentId = 11;
        $classAssessmentId = 11;

        DB::table('tbl_assessments')->insert([
            'id' => $assessmentId,
            'number_of_items' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tbl_class_assessments')->insert([
            'id' => $classAssessmentId,
            'assessment_id' => $assessmentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resultsPath = storage_path('app/public/res-'.$classAssessmentId.'.json');
        File::ensureDirectoryExists(dirname($resultsPath));

        $payload = [
            [
                'score' => 3,
                'proficiency' => 'AP',
                'answers' => [
                    1 => ['is_correct' => 1],
                    2 => ['is_correct' => 0],
                    3 => ['is_correct' => 1],
                    4 => ['is_correct' => 1],
                    5 => ['is_correct' => 0],
                ],
            ],
        ];

        File::put($resultsPath, json_encode($payload, JSON_THROW_ON_ERROR));

        $results = ClassAssessment::getScoreAnalysis($classAssessmentId);

        $this->assertSame('3.00', $results['mean']);
        $this->assertSame(0, $results['sd']);
        $this->assertSame('60.00', $results['mps']);
        $this->assertSame('60.00', $results['mastery']);

        File::delete($resultsPath);
    }
}
