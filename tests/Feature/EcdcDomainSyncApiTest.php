<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcdcDomainSyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_creates_ecdc_domains_and_competencies(): void
    {
        $payload = [
            'domains' => [
                [
                    'domain' => 'Personal Development',
                    'competencies' => [
                        ['competency' => 'Shows self-awareness'],
                        ['competency' => 'Practices self-management'],
                    ],
                ],
                [
                    'domain' => 'Social Awareness',
                    'competencies' => [
                        ['competency' => 'Demonstrates empathy'],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/ecdc/domains/sync', $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'ECDC domains synced successfully',
            ])
            ->assertJsonPath('data.0.domain', 'Personal Development');

        $this->assertDatabaseCount('tbl_ecdc_domains', 2);
        $this->assertDatabaseCount('tbl_ecdc_competencies', 3);
    }

    public function test_sync_rejects_invalid_payload(): void
    {
        $response = $this->postJson('/api/ecdc/domains/sync', [
            'domains' => [
                [
                    'domain' => 'Personal Development',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['domains.0.competencies']);
    }

    public function test_sync_is_idempotent_for_repeated_requests(): void
    {
        $payload = [
            'domains' => [
                [
                    'domain' => 'Personal Development',
                    'competencies' => [
                        ['competency' => 'Shows self-awareness'],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/ecdc/domains/sync', $payload)->assertOk();
        $this->postJson('/api/ecdc/domains/sync', $payload)->assertOk();

        $this->assertDatabaseCount('tbl_ecdc_domains', 1);
        $this->assertDatabaseCount('tbl_ecdc_competencies', 1);
    }
}
