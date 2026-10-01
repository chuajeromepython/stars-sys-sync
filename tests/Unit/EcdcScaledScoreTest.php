<?php

namespace Tests\Unit;

use App\Models\ECDC;
use App\Models\ECDCCompetency;
use App\Models\ECDCDomain;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EcdcScaledScoreTest extends TestCase
{
    /**
     * The competency count of each domain in the published instrument. The
     * youngest age bracket is generated from these counts, so the reference
     * tables only match the instrument when the library holds the same
     * number of competencies per domain.
     *
     * @var array<int, int>
     */
    private const INSTRUMENT = [
        1 => 13,
        2 => 11,
        3 => 27,
        4 => 5,
        5 => 8,
        6 => 21,
        7 => 24,
    ];

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

        foreach (self::INSTRUMENT as $index => $count) {
            $domain = ECDCDomain::create(['domain' => 'Domain '.$index]);

            for ($i = 1; $i <= $count; $i++) {
                ECDCCompetency::create([
                    'domain_id' => $domain->id,
                    'competency' => 'Competency '.$i,
                ]);
            }
        }
    }

    public function test_a_tabulated_score_is_returned_unchanged(): void
    {
        $this->assertSame(11, ECDC::getScaledScore(11, 1, 3.6));
        $this->assertSame(13, ECDC::getScaledScore(13, 1, 4.6));
        $this->assertSame(13, ECDC::getScaledScore(24, 7, 4.6));
        $this->assertSame(4, ECDC::getScaledScore(11, 1, 5.4));
        $this->assertSame(11, ECDC::getScaledScore(5, 4, 5.4));
    }

    public function test_a_score_above_the_tabulated_range_clamps_to_the_highest_value(): void
    {
        // Domain 4 tops out at 5. Adding a competency to that domain raises the
        // achievable total, which must clamp rather than raise an error.
        $this->assertSame(12, ECDC::getScaledScore(99, 4, 3.6));
        $this->assertSame(11, ECDC::getScaledScore(99, 4, 5.4));
    }

    public function test_a_domain_without_a_reference_table_scores_zero(): void
    {
        // A domain created through the ECDC Domains module has no published
        // reference table; it contributes nothing rather than breaking a result.
        $this->assertSame(0, ECDC::getScaledScore(5, 99, 3.6));
    }

    public function test_a_negative_score_clamps_to_the_lowest_value(): void
    {
        $this->assertSame(1, ECDC::getScaledScore(-3, 1, 3.6));
    }

    public function test_the_domain_of_a_competency_is_read_from_the_database(): void
    {
        $domain = ECDCDomain::create(['domain' => 'Later Domain']);
        $competency = ECDCCompetency::create([
            'domain_id' => $domain->id,
            'competency' => 'Added long after the original instrument',
        ]);

        $this->assertSame($domain->id, ECDC::getDomain($competency->id));
    }

    public function test_an_unknown_competency_has_no_domain(): void
    {
        $this->assertNull(ECDC::getDomain(9999));
    }

    public function test_the_domain_colour_palette_repeats_beyond_its_length(): void
    {
        $this->assertSame('red', ECDC::domainColor(0));
        $this->assertSame('purple', ECDC::domainColor(6));
        $this->assertSame('red', ECDC::domainColor(7));
        $this->assertSame('orange', ECDC::domainColor(8));
    }
}
