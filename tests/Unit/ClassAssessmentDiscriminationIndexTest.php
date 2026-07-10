<?php

namespace Tests\Unit;

use App\Models\ClassAssessment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClassAssessmentDiscriminationIndexTest extends TestCase
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

    public function test_discrimination_index_handles_zero_lpg_or_hpg_groups(): void
    {
        $assessmentId = 1;
        $classAssessmentId = 1;

        DB::table('tbl_assessments')->insert([
            'id' => $assessmentId,
            'number_of_items' => 2,
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
                'proficiency' => 'HP',
                'answers' => [
                    1 => ['is_correct' => 1],
                    2 => ['is_correct' => 0],
                ],
            ],
            [
                'proficiency' => 'AP',
                'answers' => [
                    1 => ['is_correct' => 1],
                    2 => ['is_correct' => 1],
                ],
            ],
        ];

        File::put($resultsPath, json_encode($payload, JSON_THROW_ON_ERROR));

        $results = ClassAssessment::getDisriminationIndex($classAssessmentId);

        $this->assertSame('1.00', $results[1]['index']);
        $this->assertSame('Very Good Item', $results[1]['classification']);
        $this->assertSame('0.00', $results[2]['index']);
        $this->assertSame('Poor Item', $results[2]['classification']);

        File::delete($resultsPath);
    }
}
