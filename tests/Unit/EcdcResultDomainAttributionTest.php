<?php

namespace Tests\Unit;

use App\Models\ECDC;
use App\Models\ECDCCompetency;
use App\Models\ECDCDomain;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The domain a recorded score is credited to used to be inferred from a hard
 * coded table of competency id ranges, so adding or removing a competency
 * silently misattributed every score. It is now read from the competency
 * record itself.
 */
class EcdcResultDomainAttributionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tbl_ecdc_domains', function (Blueprint $table): void {
            $table->id();
            $table->string('domain');
            $table->timestamps();
        });

        Schema::create('tbl_ecdc_competencies', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('domain_id');
            $table->string('competency');
            $table->timestamps();
        });

        Schema::create('tbl_ecdcs', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('classroom_id');
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('tbl_student_classrooms', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('classroom_id');
            $table->bigInteger('student_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tbl_student_ecdcs', function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('student_id');
            $table->bigInteger('ecdc_id');
            $table->bigInteger('ecdc_competency_id');
            $table->integer('score')->default(0);
            $table->timestamps();
        });
    }

    public function test_results_carry_the_domain_of_the_competency_that_was_scored(): void
    {
        $first = ECDCDomain::create(['domain' => 'First Domain']);
        $second = ECDCDomain::create(['domain' => 'Second Domain']);

        $firstCompetency = ECDCCompetency::create([
            'domain_id' => $first->id,
            'competency' => 'Sits upright',
        ]);
        $secondCompetency = ECDCCompetency::create([
            'domain_id' => $second->id,
            'competency' => 'Grasps a crayon',
        ]);

        DB::table('tbl_ecdcs')->insert(['id' => 1, 'classroom_id' => 1, 'date' => '2026-01-15']);
        DB::table('tbl_student_classrooms')->insert(['id' => 1, 'classroom_id' => 1, 'student_id' => 7]);

        DB::table('tbl_student_ecdcs')->insert([
            ['student_id' => 7, 'ecdc_id' => 1, 'ecdc_competency_id' => $firstCompetency->id, 'score' => 1],
            ['student_id' => 7, 'ecdc_id' => 1, 'ecdc_competency_id' => $secondCompetency->id, 'score' => 0],
        ]);

        $results = ECDC::saveResults(1);

        $this->assertSame(
            $first->id,
            $results[7][$firstCompetency->id]['domain_id']
        );
        $this->assertSame(
            $second->id,
            $results[7][$secondCompetency->id]['domain_id']
        );
        $this->assertSame($firstCompetency->id, $results[7][$firstCompetency->id]['student_ecdc_id']);
    }

    public function test_a_competency_created_outside_the_legacy_id_ranges_keeps_its_domain(): void
    {
        // Ids from 110 upwards fall outside every range the old lookup covered,
        // so the competency is forced onto an id the old code could not map.
        $domain = ECDCDomain::create(['domain' => 'Late Domain']);

        $competency = new ECDCCompetency;
        $competency->id = 110;
        $competency->domain_id = $domain->id;
        $competency->competency = 'Added after the original instrument';
        $competency->save();

        $this->assertSame(110, $competency->id);
        $this->assertSame($domain->id, ECDC::getDomain(110));
    }

    public function test_scores_of_different_students_do_not_leak_into_one_another(): void
    {
        $domain = ECDCDomain::create(['domain' => 'Domain']);
        $competency = ECDCCompetency::create([
            'domain_id' => $domain->id,
            'competency' => 'Competency',
        ]);

        DB::table('tbl_ecdcs')->insert(['id' => 1, 'classroom_id' => 1, 'date' => '2026-01-15']);
        DB::table('tbl_student_classrooms')->insert([
            ['id' => 1, 'classroom_id' => 1, 'student_id' => 7],
            ['id' => 2, 'classroom_id' => 1, 'student_id' => 8],
        ]);

        DB::table('tbl_student_ecdcs')->insert(['student_id' => 7, 'ecdc_id' => 1, 'ecdc_competency_id' => $competency->id, 'score' => 1]);

        $results = ECDC::saveResults(1);

        $this->assertArrayHasKey(7, $results);
        $this->assertArrayNotHasKey(8, $results);
    }
}
