<?php

namespace Tests\Feature;

use App\Models\ECDC;
use App\Models\StudentECDC;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EcdcResultUploadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uploads_an_individual_ecdc_result(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $response = $this->postJson('/api/ecdc/upload', $this->payload($context, [
            [
                'lrn' => '100000000001',
                'last_ticked_at' => '2026-09-29T09:15:00+08:00',
                'responses' => $this->responses(
                    [$context['competency_ids'][0], $context['competency_ids'][1], $context['competency_ids'][2]],
                    ['1', '-', '*']
                ),
            ],
        ]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'ECDC results uploaded successfully.',
                'data' => [
                    'classroom_id' => $context['classroom_id'],
                    'academic_year_id' => $context['academic_year_id'],
                    'period' => '1',
                    'date' => '2026-09-29',
                    'students_uploaded' => 1,
                    'responses_saved' => 2,
                    'responses_skipped' => 1,
                ],
            ]);

        $this->assertNull($response->json('errors'));

        $ecdc = DB::table('tbl_ecdcs')->where('classroom_id', $context['classroom_id'])->first();

        $this->assertNotNull($ecdc);
        $this->assertSame('1', $ecdc->period);
        $this->assertSame('1', $ecdc->source);
        $this->assertSame('2026-09-29', $ecdc->date);
        $this->assertSame($context['teacher_id'], (int) $ecdc->teacher_id);
        $this->assertSame($context['academic_year_id'], (int) $ecdc->academic_year_id);

        $scores = DB::table('tbl_student_ecdcs')
            ->where('ecdc_id', $ecdc->id)
            ->pluck('score', 'ecdc_competency_id')
            ->all();

        $this->assertCount(2, $scores);
        $this->assertSame(1, (int) $scores[$context['competency_ids'][0]]);
        $this->assertSame(0, (int) $scores[$context['competency_ids'][1]]);
        $this->assertArrayNotHasKey($context['competency_ids'][2], $scores);

        Storage::disk('public')->assertExists('ecdc-'.$ecdc->id.'.json');
    }

    public function test_it_uploads_results_for_several_students_in_one_request(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [
            [
                'lrn' => '100000000001',
                'last_ticked_at' => '2026-09-29',
                'responses' => $this->responses($context['competency_ids'], ['1', '1', '-', '*']),
            ],
            [
                'lrn' => '100000000002',
                'last_ticked_at' => '2026-09-29',
                'responses' => $this->responses($context['competency_ids'], ['-', '-', '*', '*']),
            ],
        ]))->assertOk()->assertJson([
            'data' => [
                'students_uploaded' => 2,
                'responses_saved' => 5,
                'responses_skipped' => 3,
            ],
        ]);

        $this->assertDatabaseCount('tbl_ecdcs', 1);
        $this->assertDatabaseCount('tbl_student_ecdcs', 5);

        $ecdc_id = DB::table('tbl_ecdcs')->value('id');

        $this->assertSame(2, DB::table('tbl_student_ecdcs')->where('ecdc_id', $ecdc_id)->distinct()->count('student_id'));
    }

    public function test_it_replaces_previous_answers_when_the_same_student_is_uploaded_again(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $competency_ids = $context['competency_ids'];

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'last_ticked_at' => '2026-09-29',
            'responses' => $this->responses([$competency_ids[0], $competency_ids[1]], ['1', '-']),
        ]]))->assertOk();

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'last_ticked_at' => '2026-10-02',
            'responses' => $this->responses([$competency_ids[0], $competency_ids[1], $competency_ids[2]], ['-', '1', '*']),
        ]]))->assertOk()->assertJson([
            'data' => [
                'date' => '2026-10-02',
                'responses_saved' => 2,
                'responses_skipped' => 1,
            ],
        ]);

        $this->assertDatabaseCount('tbl_ecdcs', 1);
        $this->assertDatabaseCount('tbl_student_ecdcs', 2);

        $scores = DB::table('tbl_student_ecdcs')->pluck('score', 'ecdc_competency_id')->all();

        $this->assertSame(0, (int) $scores[$competency_ids[0]]);
        $this->assertSame(1, (int) $scores[$competency_ids[1]]);
        $this->assertArrayNotHasKey($competency_ids[2], $scores);
        $this->assertSame('2026-10-02', DB::table('tbl_ecdcs')->value('date'));
    }

    public function test_it_stores_named_and_numeric_periods_as_one_event_per_period(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'last_ticked_at' => '2026-09-29',
            'responses' => $this->responses($context['competency_ids'], ['1', '-', '*', '1']),
        ]], 'MOSY'))->assertOk()->assertJson(['data' => ['period' => '2']]);

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000002',
            'last_ticked_at' => '2027-02-11',
            'responses' => $this->responses($context['competency_ids'], ['1', '1', '-', '-']),
        ]], '3'))->assertOk()->assertJson(['data' => ['period' => '3']]);

        $this->assertDatabaseCount('tbl_ecdcs', 2);
        $this->assertSame(['2', '3'], DB::table('tbl_ecdcs')->orderBy('id')->pluck('period')->all());
        $this->assertDatabaseCount('tbl_student_ecdcs', 7);
    }

    public function test_it_rejects_a_status_that_is_not_present_not_present_or_not_tested(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'responses' => $this->responses([$context['competency_ids'][0]], ['2']),
        ]]))->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Validation failed'])
            ->assertJsonValidationErrors(['students.0.responses.0.status']);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
        $this->assertDatabaseCount('tbl_student_ecdcs', 0);
    }

    public function test_it_rejects_a_competency_that_does_not_exist(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'responses' => $this->responses([9999], ['1']),
        ]]))->assertStatus(422)->assertJsonValidationErrors(['students.0.responses.0.competency_id']);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
    }

    public function test_it_rejects_the_same_competency_answered_twice(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $competency_id = $context['competency_ids'][0];

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'responses' => $this->responses([$competency_id, $competency_id], ['1', '-']),
        ]]))->assertStatus(422)->assertJsonValidationErrors(['students.0.responses.1.competency_id']);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
    }

    public function test_it_rejects_a_period_the_module_does_not_use(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'responses' => $this->responses([$context['competency_ids'][0]], ['1']),
        ]], 'QUARTER 4'))->assertStatus(422)->assertJsonValidationErrors(['period']);
    }

    public function test_it_rejects_a_payload_without_students(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, []))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['students']);
    }

    public function test_it_rejects_an_lrn_that_is_not_in_the_classroom_and_saves_nothing(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [
            [
                'lrn' => '100000000001',
                'responses' => $this->responses($context['competency_ids'], ['1', '-', '*', '1']),
            ],
            [
                'lrn' => '199999999999',
                'responses' => $this->responses($context['competency_ids'], ['1', '1', '-', '*']),
            ],
        ]))->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'One or more students cannot be uploaded to this classroom.',
            ])
            ->assertJsonValidationErrors(['students.1.lrn']);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
        $this->assertDatabaseCount('tbl_student_ecdcs', 0);
    }

    public function test_it_rejects_the_same_lrn_submitted_twice(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [
            [
                'lrn' => '100000000001',
                'responses' => $this->responses($context['competency_ids'], ['1', '-', '*', '1']),
            ],
            [
                'lrn' => '1000 000000 01',
                'responses' => $this->responses($context['competency_ids'], ['1', '1', '-', '*']),
            ],
        ]))->assertStatus(422)->assertJsonValidationErrors(['students.1.lrn']);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
    }

    public function test_it_requires_an_authenticated_session(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'responses' => $this->responses($context['competency_ids'], ['1', '-', '*', '1']),
        ]]))->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated request. Please login first.',
            ]);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
    }

    public function test_it_rejects_a_user_id_that_does_not_match_the_active_session(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $other_teacher_user_id = $this->createUser('Teacher', 'M', 'Other');
        $this->actingAs(User::findOrFail($other_teacher_user_id));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'responses' => $this->responses($context['competency_ids'], ['1', '-', '*', '1']),
        ]]))->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Submitted user_id does not match the active session.',
            ]);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
    }

    public function test_it_rejects_an_account_without_a_teacher_record(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $scholar_user_id = $this->createUser('Student', 'F', 'Scholar');
        $this->actingAs(User::findOrFail($scholar_user_id));

        $payload = $this->payload($context, [[
            'lrn' => '100000000001',
            'responses' => $this->responses($context['competency_ids'], ['1', '-', '*', '1']),
        ]]);
        $payload['user_id'] = $scholar_user_id;

        $this->postJson('/api/ecdc/upload', $payload)->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only teacher accounts can upload ECDC results.',
            ]);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
    }

    public function test_it_rejects_a_classroom_from_another_school(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $other_school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-ECDC-002',
            'name' => 'Other School',
            'address' => 'School Address',
            'school_category_id' => 1,
            'school_type_id' => 1,
            'district_id' => 1,
        ]);
        $other_classroom_id = $this->createClassroom($other_school_id, $context['academic_year_id']);
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $payload = $this->payload($context, [[
            'lrn' => '100000000001',
            'responses' => $this->responses($context['competency_ids'], ['1', '-', '*', '1']),
        ]]);
        $payload['classroom_id'] = $other_classroom_id;

        $this->postJson('/api/ecdc/upload', $payload)->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'The selected classroom does not belong to your school.',
            ]);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
    }

    public function test_it_rejects_an_upload_without_an_active_academic_year(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        DB::table('tbl_academic_years')->where('id', $context['academic_year_id'])->update(['is_active' => 0]);
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'responses' => $this->responses($context['competency_ids'], ['1', '-', '*', '1']),
        ]]))->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'No active academic year was found.',
            ]);

        $this->assertDatabaseCount('tbl_ecdcs', 0);
    }

    public function test_it_refuses_to_replace_results_that_were_encoded_in_the_web_app(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();

        $encoded_ecdc_id = DB::table('tbl_ecdcs')->insertGetId([
            'classroom_id' => $context['classroom_id'],
            'academic_year_id' => $context['academic_year_id'],
            'period' => '1',
            'teacher_id' => $context['teacher_id'],
            'source' => '2',
            'date' => '2026-09-01',
        ]);
        DB::table('tbl_student_ecdcs')->insert([
            'student_id' => (int) DB::table('tbl_students')->where('lrn', '100000000001')->value('id'),
            'ecdc_id' => $encoded_ecdc_id,
            'ecdc_competency_id' => $context['competency_ids'][0],
            'score' => 1,
        ]);

        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'last_ticked_at' => '2026-09-29',
            'responses' => $this->responses($context['competency_ids'], ['-', '-', '-', '-']),
        ]]))->assertStatus(422)
            ->assertJson([
                'success' => false,
                'errors' => [
                    'period' => ['Period 1 already has encoded results for this classroom.'],
                ],
            ])
            ->assertJsonValidationErrors(['period']);

        $this->assertDatabaseCount('tbl_ecdcs', 1);
        $this->assertDatabaseCount('tbl_student_ecdcs', 1);
        $this->assertSame(1, (int) DB::table('tbl_student_ecdcs')->value('score'));
    }

    public function test_the_uploaded_result_is_readable_by_the_cards_and_reports(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $response = $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'last_ticked_at' => '2027-06-10',
            'responses' => $this->responses($context['competency_ids'], ['1', '1', '1', '-']),
        ]], 'EOSY'));

        $ecdc_id = $response->json('data.ecdc_id');
        $student_id = (int) DB::table('tbl_students')->where('lrn', '100000000001')->value('id');

        // The student card and the SF5-K report only read uploaded events for
        // the active academic year, so the upload has to satisfy that filter.
        $this->assertSame($ecdc_id, StudentECDC::getECD([
            'academic_year_id' => $context['academic_year_id'],
            'classroom_id' => $context['classroom_id'],
            'student_id' => $student_id,
        ])->where('period', '3')->where('source', 1)->value('ecdc_id'));

        $result = ECDC::getJsonResult($ecdc_id)[$student_id];

        $this->assertSame('100000000001', $result['lrn']);
        $this->assertStringContainsString('Able, Alex', $result['name']);
        $this->assertSame('2027-06-10', $result['date_tested']);
        $this->assertSame(2, $result['domains'][1]['score']);
        $this->assertSame(1, $result['domains'][2]['score']);
        $this->assertCount(2, $result['domains']);
        $this->assertArrayHasKey('standard_score', $result);
        $this->assertArrayHasKey('interpretation', $result);
    }

    public function test_sync_reports_nothing_recorded_before_the_first_upload(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $response = $this->postJson('/api/ecdc/results/sync', [
            'classroom_id' => $context['classroom_id'],
            'user_id' => $context['teacher_user_id'],
        ])->assertOk()->assertJson([
            'success' => true,
            'message' => 'ECDC results synced successfully.',
            'data' => [
                'classroom_id' => $context['classroom_id'],
                'academic_year_id' => $context['academic_year_id'],
                'competencies_per_sheet' => 4,
                'periods' => [],
            ],
        ]);

        $this->assertSame([], $response->json('data.students.0.periods'));
        $this->assertSame('100000000001', $response->json('data.students.0.lrn'));
        $this->assertSame('100000000002', $response->json('data.students.1.lrn'));
        $this->assertNull($response->json('errors'));
    }

    public function test_sync_reports_recorded_results_per_period_and_learner(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();
        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000001',
            'last_ticked_at' => '2026-09-29',
            'responses' => $this->responses($context['competency_ids'], ['1', '-', '*', '*']),
        ]]))->assertOk();

        $this->postJson('/api/ecdc/upload', $this->payload($context, [[
            'lrn' => '100000000002',
            'last_ticked_at' => '2027-02-11',
            'responses' => $this->responses($context['competency_ids'], ['1', '1', '-', '-']),
        ]], 'EOSY'))->assertOk();

        $response = $this->postJson('/api/ecdc/results/sync', [
            'classroom_id' => $context['classroom_id'],
            'user_id' => $context['teacher_user_id'],
        ])->assertOk()->assertJson([
            'data' => [
                'periods' => [
                    ['period' => '1', 'label' => 'BOSY', 'date' => '2026-09-29', 'source' => 1, 'students_recorded' => 1, 'responses_saved' => 2],
                    ['period' => '3', 'label' => 'EOSY', 'date' => '2027-02-11', 'source' => 1, 'students_recorded' => 1, 'responses_saved' => 4],
                ],
            ],
        ]);

        // Only recorded answers are counted, so the two not tested answers of
        // the first sheet stay out and the app can flag the sheet as partial.
        $bosy = collect($response->json('data.students.0.periods'));

        $this->assertCount(1, $bosy);
        $this->assertSame('1', $bosy[0]['period']);
        $this->assertSame('BOSY', $bosy[0]['label']);
        $this->assertSame(2, $bosy[0]['responses_saved']);
        $this->assertNotNull($bosy[0]['last_answered_at']);

        $eosy = collect($response->json('data.students.1.periods'));

        $this->assertCount(1, $eosy);
        $this->assertSame('3', $eosy[0]['period']);
        $this->assertSame(4, $eosy[0]['responses_saved']);
    }

    public function test_sync_marks_encoded_sheets_so_the_app_avoids_uploading_over_them(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();

        $encoded_ecdc_id = DB::table('tbl_ecdcs')->insertGetId([
            'classroom_id' => $context['classroom_id'],
            'academic_year_id' => $context['academic_year_id'],
            'period' => '2',
            'teacher_id' => $context['teacher_id'],
            'source' => '2',
            'date' => '2026-11-05',
        ]);
        DB::table('tbl_student_ecdcs')->insert([
            'student_id' => (int) DB::table('tbl_students')->where('lrn', '100000000001')->value('id'),
            'ecdc_id' => $encoded_ecdc_id,
            'ecdc_competency_id' => $context['competency_ids'][0],
            'score' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $response = $this->postJson('/api/ecdc/results/sync', [
            'classroom_id' => $context['classroom_id'],
            'user_id' => $context['teacher_user_id'],
        ])->assertOk()->assertJson([
            'data' => [
                'periods' => [
                    ['ecdc_id' => $encoded_ecdc_id, 'period' => '2', 'label' => 'MOSY', 'source' => 2, 'students_recorded' => 1, 'responses_saved' => 1],
                ],
            ],
        ]);

        $this->assertSame($encoded_ecdc_id, $response->json('data.students.0.periods.0.ecdc_id'));
        $this->assertSame(2, $response->json('data.students.0.periods.0.source'));
        $this->assertSame([], $response->json('data.students.1.periods'));
    }

    public function test_sync_ignores_other_academic_years(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();

        $previous_ecdc_id = DB::table('tbl_ecdcs')->insertGetId([
            'classroom_id' => $context['classroom_id'],
            'academic_year_id' => DB::table('tbl_academic_years')->insertGetId([
                'from' => '2024',
                'to' => '2025',
                'is_active' => 0,
            ]),
            'period' => '1',
            'teacher_id' => $context['teacher_id'],
            'source' => '1',
            'date' => '2024-08-01',
        ]);
        DB::table('tbl_student_ecdcs')->insert([
            'student_id' => (int) DB::table('tbl_students')->where('lrn', '100000000001')->value('id'),
            'ecdc_id' => $previous_ecdc_id,
            'ecdc_competency_id' => $context['competency_ids'][0],
            'score' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $response = $this->postJson('/api/ecdc/results/sync', [
            'classroom_id' => $context['classroom_id'],
            'user_id' => $context['teacher_user_id'],
        ])->assertOk()->assertJson([
            'data' => [
                'academic_year_id' => $context['academic_year_id'],
                'periods' => [],
            ],
        ]);

        $this->assertSame([], $response->json('data.students.0.periods'));
    }

    public function test_sync_rejects_invalid_unauthenticated_and_foreign_requests(): void
    {
        Storage::fake('public');

        $context = $this->seedEcdcContext();

        $this->postJson('/api/ecdc/results/sync', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['classroom_id', 'user_id']);

        $this->postJson('/api/ecdc/results/sync', [
            'classroom_id' => $context['classroom_id'],
            'user_id' => $context['teacher_user_id'],
        ])->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated request. Please login first.',
        ]);

        $this->actingAs(User::findOrFail($this->createUser('Teacher', 'M', 'Stranger')));

        $this->postJson('/api/ecdc/results/sync', [
            'classroom_id' => $context['classroom_id'],
            'user_id' => $context['teacher_user_id'],
        ])->assertStatus(403)->assertJson([
            'message' => 'Submitted user_id does not match the active session.',
        ]);

        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $scholar_user_id = $this->createUser('Student', 'F', 'Scholar');
        $this->actingAs(User::findOrFail($scholar_user_id));

        $this->postJson('/api/ecdc/results/sync', [
            'classroom_id' => $context['classroom_id'],
            'user_id' => $scholar_user_id,
        ])->assertStatus(403)->assertJson([
            'message' => 'Only teacher accounts can sync ECDC results.',
        ]);

        $this->actingAs(User::findOrFail($context['teacher_user_id']));

        $other_school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-ECDC-003',
            'name' => 'Third School',
            'address' => 'School Address',
            'school_category_id' => 1,
            'school_type_id' => 1,
            'district_id' => 1,
        ]);

        $this->postJson('/api/ecdc/results/sync', [
            'classroom_id' => $this->createClassroom($other_school_id, $context['academic_year_id']),
            'user_id' => $context['teacher_user_id'],
        ])->assertStatus(403)->assertJson([
            'message' => 'The selected classroom does not belong to your school.',
        ]);
    }

    /**
     * Builds the request body the mobile app sends for a classroom.
     *
     * @param  array<int, array{lrn: string, last_ticked_at?: string, responses: array<int, array{competency_id: int, status: string}>}>  $students
     * @return array<string, mixed>
     */
    private function payload(array $context, array $students, string $period = 'BOSY'): array
    {
        return [
            'classroom_id' => $context['classroom_id'],
            'user_id' => $context['teacher_user_id'],
            'period' => $period,
            'students' => $students,
        ];
    }

    /**
     * Pairs competency ids with the answer statuses captured on the sheet.
     *
     * @param  array<int, int>  $competency_ids
     * @param  array<int, string>  $statuses
     * @return array<int, array{competency_id: int, status: string}>
     */
    private function responses(array $competency_ids, array $statuses): array
    {
        $responses = [];

        foreach ($competency_ids as $index => $competency_id) {
            $responses[] = [
                'competency_id' => $competency_id,
                'status' => $statuses[$index],
            ];
        }

        return $responses;
    }

    /**
     * Seeds a Kinder classroom with a teacher, two learners, and the four
     * ECDC competencies a synchronised domain sheet would contain.
     *
     * @return array{academic_year_id: int, classroom_id: int, competency_ids: array<int, int>, teacher_id: int, teacher_user_id: int}
     */
    private function seedEcdcContext(): array
    {
        $division_id = DB::table('tbl_divisions')->insertGetId(['name' => 'Test Division']);
        $district_id = DB::table('tbl_districts')->insertGetId([
            'name' => 'Test District',
            'division_id' => $division_id,
        ]);
        $school_id = DB::table('tbl_schools')->insertGetId([
            'code' => 'SCH-ECDC-001',
            'name' => 'Test School',
            'address' => 'School Address',
            'school_category_id' => 1,
            'school_type_id' => 1,
            'district_id' => $district_id,
        ]);
        $academic_year_id = DB::table('tbl_academic_years')->insertGetId([
            'from' => '2026',
            'to' => '2027',
            'is_active' => 1,
        ]);
        $classroom_id = $this->createClassroom($school_id, $academic_year_id);

        $teacher_user_id = $this->createUser('Teacher', 'M', 'Adviser');
        $teacher_id = DB::table('tbl_teachers')->insertGetId([
            'email' => 'adviser@example.test',
            'user_id' => $teacher_user_id,
            'school_id' => $school_id,
        ]);

        $this->createStudent($school_id, $classroom_id, '100000000001', 'Alex', 'Able', 'M');
        $this->createStudent($school_id, $classroom_id, '100000000002', 'Bailey', 'Baker', 'F');

        $competency_ids = [];

        foreach ([
            'Health and Physical Development' => [
                'Takes a bath with little help',
                'Brushes teeth with little help',
            ],
            'Social and Emotional Development' => [
                'Plays and works with other children',
                'Expresses emotions appropriately',
            ],
        ] as $domain => $competencies) {
            $domain_id = DB::table('tbl_ecdc_domains')->insertGetId([
                'domain' => $domain,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($competencies as $competency) {
                $competency_ids[] = DB::table('tbl_ecdc_competencies')->insertGetId([
                    'domain_id' => $domain_id,
                    'competency' => $competency,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return [
            'academic_year_id' => $academic_year_id,
            'classroom_id' => $classroom_id,
            'competency_ids' => $competency_ids,
            'teacher_id' => $teacher_id,
            'teacher_user_id' => $teacher_user_id,
        ];
    }

    private function createClassroom(int $school_id, int $academic_year_id): int
    {
        $grade_level_id = DB::table('tbl_grade_levels')->insertGetId(['level' => 'Kinder']);
        $section_id = DB::table('tbl_sections')->insertGetId([
            'section' => 'Maple',
            'school_id' => $school_id,
        ]);

        return DB::table('tbl_classrooms')->insertGetId([
            'section_id' => $section_id,
            'grade_level_id' => $grade_level_id,
            'academic_year_id' => $academic_year_id,
            'school_id' => $school_id,
        ]);
    }

    private function createUser(string $classification, string $gender, string $lastName): int
    {
        $person_id = DB::table('tbl_persons')->insertGetId([
            'first_name' => 'Test',
            'middle_name' => '',
            'last_name' => $lastName,
            'suffix' => '',
            'gender' => $gender,
            'birth_date' => '2019-01-01',
        ]);

        return DB::table('tbl_users')->insertGetId([
            'username' => strtolower($lastName).'-'.uniqid().'@example.test',
            'password' => bcrypt('password'),
            'classification' => $classification,
            'status' => 1,
            'person_id' => $person_id,
        ]);
    }

    private function createStudent(int $school_id, int $classroom_id, string $lrn, string $first_name, string $last_name, string $gender): int
    {
        $user_id = $this->createUser('Student', $gender, $last_name);
        DB::table('tbl_persons')->where('id', DB::table('tbl_users')->where('id', $user_id)->value('person_id'))
            ->update(['first_name' => $first_name]);

        $student_id = DB::table('tbl_students')->insertGetId([
            'lrn' => $lrn,
            'user_id' => $user_id,
            'school_id' => $school_id,
            'email' => strtolower($last_name).'@example.test',
        ]);

        DB::table('tbl_student_classrooms')->insert([
            'student_id' => $student_id,
            'classroom_id' => $classroom_id,
            'status' => 1,
        ]);

        return $student_id;
    }
}
