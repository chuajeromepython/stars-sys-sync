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

    public function test_every_item_is_classified_even_inside_the_index_gap(): void
    {
        $assessmentId = 2;
        $classAssessmentId = 2;

        DB::table('tbl_assessments')->insert([
            'id' => $assessmentId,
            'number_of_items' => 1,
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

        // Sweep several group sizes so a range of index percentages is produced,
        // including the 19 <= $percentage < 20 band that the old if/elseif chain
        // fell through without setting $classification/$recommendation.
        $payload = [];

        for ($high = 1; $high <= 40; $high++) {
            for ($low = 0; $low <= $high; $low++) {
                $payload[] = ['proficiency' => 'HP', 'answers' => [1 => ['is_correct' => 1]]];
            }

            for ($low = 0; $low <= $high; $low++) {
                $payload[] = [
                    'proficiency' => 'LP',
                    'answers' => [1 => ['is_correct' => $low < $high ? 1 : 0]],
                ];
            }
        }

        File::put($resultsPath, json_encode($payload, JSON_THROW_ON_ERROR));

        $results = ClassAssessment::getDisriminationIndex($classAssessmentId);

        $this->assertCount(1, $results);

        foreach ($results as $result) {
            $this->assertArrayHasKey('classification', $result);
            $this->assertArrayHasKey('recommendation', $result);
            $this->assertNotSame('', $result['classification']);
            $this->assertNotSame('', $result['recommendation']);
        }

        File::delete($resultsPath);
    }

    public function test_get_results_returns_an_empty_array_when_the_file_is_missing(): void
    {
        $path = storage_path('app/public/res-9999.json');

        if (File::exists($path)) {
            File::delete($path);
        }

        // Previously Storage::get() threw a FileNotFoundException here, which
        // surfaced as a 500 on every report for an unassessed class.
        $this->assertSame([], ClassAssessment::getResults(9999));
    }
}
