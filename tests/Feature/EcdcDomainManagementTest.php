<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ECDCCompetency;
use App\Models\ECDCDomain;
use App\Services\Rbac\RolePermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class EcdcDomainManagementTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/ecdc_domains')->assertRedirect('/login');
        $this->get('/ecdc_domains/1')->assertRedirect('/login');
        $this->post('/ecdc_domains/store')->assertRedirect('/login');
    }

    public function test_roles_without_the_module_cannot_reach_it(): void
    {
        $domain = $this->createDomain('Gross Motor Skills');

        foreach ([Role::Teacher, Role::SchoolHead, Role::DivisionSupervisor, Role::DistrictSupervisor] as $role) {
            $user = $this->userWithRole($role);

            $this->actingAs($user)->get('/ecdc_domains')->assertRedirect('/forbidden');
            $this->actingAs($user)->get('/ecdc_domains/'.$domain->id)->assertRedirect('/forbidden');
            $this->actingAs($user)->post('/ecdc_domains/store')->assertRedirect('/forbidden');
            $this->actingAs($user)->post('/ecdc_domains/update')->assertRedirect('/forbidden');
            $this->actingAs($user)->post('/ecdc_domains/destroy')->assertRedirect('/forbidden');
            $this->actingAs($user)
                ->post('/ecdc_domains/'.$domain->id.'/competencies/store')
                ->assertRedirect('/forbidden');
        }
    }

    public function test_a_division_administrator_sees_the_domain_listing(): void
    {
        $domain = $this->createDomain('Gross Motor Skills');
        $this->createDomain('Fine Motor Skills');

        $response = $this->actingAs($this->userWithRole(Role::DivisionAdministrator))->get('/ecdc_domains');

        $response->assertOk();
        $response->assertSee('id="dt_ecdc_domains"', false);
        $response->assertSee('Gross Motor Skills');
        $response->assertSee('Fine Motor Skills');
        $response->assertSee('0 competencies', false);
        $response->assertSee('Add Domain');
        $response->assertSee('data-destroy_id="'.$domain->id.'"', false);
    }

    public function test_a_division_administrator_creates_a_domain(): void
    {
        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->post('/ecdc_domains/store', ['domain' => 'Language Domain'])
            ->assertRedirect('/ecdc_domains')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tbl_ecdc_domains', ['domain' => 'Language Domain']);
    }

    public function test_a_domain_name_is_required(): void
    {
        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains')
            ->post('/ecdc_domains/store', ['domain' => ''])
            ->assertSessionHasErrors('domain');

        $this->assertDatabaseCount('tbl_ecdc_domains', 0);
    }

    public function test_a_duplicate_domain_name_is_rejected_regardless_of_case(): void
    {
        $this->createDomain('Language Domain');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains')
            ->post('/ecdc_domains/store', ['domain' => 'language domain'])
            ->assertSessionHasErrors('error');

        $this->assertDatabaseCount('tbl_ecdc_domains', 1);
    }

    public function test_a_division_administrator_updates_a_domain(): void
    {
        $domain = $this->createDomain('Old Name');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains')
            ->post('/ecdc_domains/update', ['id' => $domain->id, 'domain' => 'New Name'])
            ->assertRedirect('/ecdc_domains')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tbl_ecdc_domains', ['id' => $domain->id, 'domain' => 'New Name']);
    }

    public function test_updating_a_domain_to_an_existing_name_is_rejected(): void
    {
        $domain = $this->createDomain('Old Name');
        $this->createDomain('Taken Name');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains')
            ->post('/ecdc_domains/update', ['id' => $domain->id, 'domain' => 'Taken Name'])
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('tbl_ecdc_domains', ['id' => $domain->id, 'domain' => 'Old Name']);
    }

    public function test_a_domain_without_competencies_is_deleted(): void
    {
        $domain = $this->createDomain('Disposable Domain');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains')
            ->post('/ecdc_domains/destroy', ['id' => $domain->id])
            ->assertRedirect('/ecdc_domains')
            ->assertSessionHas('success');

        $this->assertDatabaseCount('tbl_ecdc_domains', 0);
    }

    public function test_a_domain_holding_competencies_is_not_deleted(): void
    {
        $domain = $this->createDomain('Populated Domain');
        $this->createCompetency($domain);

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains')
            ->post('/ecdc_domains/destroy', ['id' => $domain->id])
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('tbl_ecdc_domains', ['id' => $domain->id]);
    }

    public function test_the_domain_page_lists_only_that_domains_competencies(): void
    {
        $gross = $this->createDomain('Gross Motor Skills');
        $this->createCompetency($gross, 'Walks with support');
        $fine = $this->createDomain('Fine Motor Skills');
        $this->createCompetency($fine, 'Grasps a crayon');

        $response = $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->get('/ecdc_domains/'.$gross->id);

        $response->assertOk();
        $response->assertSee('id="dt_ecdc_competencies"', false);
        $response->assertSee('Walks with support');
        $response->assertDontSee('Grasps a crayon');
    }

    public function test_a_division_administrator_adds_a_competency_to_a_domain(): void
    {
        $domain = $this->createDomain('Language Domain');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains/'.$domain->id)
            ->post('/ecdc_domains/'.$domain->id.'/competencies/store', ['competency' => 'Responds to own name'])
            ->assertRedirect('/ecdc_domains/'.$domain->id)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tbl_ecdc_competencies', [
            'domain_id' => $domain->id,
            'competency' => 'Responds to own name',
        ]);
    }

    public function test_a_duplicate_competency_within_a_domain_is_rejected(): void
    {
        $domain = $this->createDomain('Language Domain');
        $this->createCompetency($domain, 'Responds to own name');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains/'.$domain->id)
            ->post('/ecdc_domains/'.$domain->id.'/competencies/store', ['competency' => 'responds to own name'])
            ->assertSessionHasErrors('error');

        $this->assertSame(1, ECDCCompetency::where('domain_id', $domain->id)->count());
    }

    public function test_the_same_competency_text_may_appear_under_another_domain(): void
    {
        $first = $this->createDomain('First Domain');
        $this->createCompetency($first, 'Sits upright');
        $second = $this->createDomain('Second Domain');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains/'.$second->id)
            ->post('/ecdc_domains/'.$second->id.'/competencies/store', ['competency' => 'Sits upright'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tbl_ecdc_competencies', [
            'domain_id' => $second->id,
            'competency' => 'Sits upright',
        ]);
    }

    public function test_a_division_administrator_updates_a_competency(): void
    {
        $domain = $this->createDomain('Language Domain');
        $competency = $this->createCompetency($domain, 'Old text');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains/'.$domain->id)
            ->post('/ecdc_domains/'.$domain->id.'/competencies/update', [
                'id' => $competency->id,
                'competency' => 'New text',
            ])
            ->assertRedirect('/ecdc_domains/'.$domain->id)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tbl_ecdc_competencies', [
            'id' => $competency->id,
            'competency' => 'New text',
        ]);
    }

    public function test_a_competency_of_another_domain_cannot_be_updated_through_this_domain(): void
    {
        $first = $this->createDomain('First Domain');
        $second = $this->createDomain('Second Domain');
        $competency = $this->createCompetency($first, 'Belongs to the first domain');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains/'.$second->id)
            ->post('/ecdc_domains/'.$second->id.'/competencies/update', [
                'id' => $competency->id,
                'competency' => 'Hijacked',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('tbl_ecdc_competencies', [
            'id' => $competency->id,
            'competency' => 'Belongs to the first domain',
        ]);
    }

    public function test_an_unused_competency_is_deleted(): void
    {
        $domain = $this->createDomain('Language Domain');
        $competency = $this->createCompetency($domain, 'Unused');

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains/'.$domain->id)
            ->post('/ecdc_domains/'.$domain->id.'/competencies/destroy', ['id' => $competency->id])
            ->assertRedirect('/ecdc_domains/'.$domain->id)
            ->assertSessionHas('success');

        $this->assertDatabaseCount('tbl_ecdc_competencies', 0);
    }

    public function test_a_competency_referenced_by_recorded_results_is_not_deleted(): void
    {
        $domain = $this->createDomain('Language Domain');
        $competency = $this->createCompetency($domain, 'Scored');

        DB::table('tbl_student_ecdcs')->insert([
            'student_id' => 1,
            'ecdc_id' => 1,
            'ecdc_competency_id' => $competency->id,
            'score' => 1,
        ]);

        $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->from('/ecdc_domains/'.$domain->id)
            ->post('/ecdc_domains/'.$domain->id.'/competencies/destroy', ['id' => $competency->id])
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('tbl_ecdc_competencies', ['id' => $competency->id]);
    }

    public function test_the_domain_page_marks_competencies_already_in_use(): void
    {
        $domain = $this->createDomain('Language Domain');
        $scored = $this->createCompetency($domain, 'Scored');
        $this->createCompetency($domain, 'Not yet used');

        DB::table('tbl_student_ecdcs')->insert([
            'student_id' => 1,
            'ecdc_id' => 1,
            'ecdc_competency_id' => $scored->id,
            'score' => 1,
        ]);

        $response = $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->get('/ecdc_domains/'.$domain->id);

        $response->assertOk();
        $response->assertSee('1 result', false);
        $response->assertSee('Not yet used');
    }

    public function test_only_the_division_administrator_manages_the_domain_library(): void
    {
        $this->provisionRbac();

        $role = SpatieRole::findByName(Role::DivisionAdministrator->value);

        $this->assertTrue($role->hasPermissionTo('ecdc_domains.view'));
        $this->assertTrue($role->hasPermissionTo('ecdc_domains.manage'));

        foreach (Role::cases() as $candidate) {
            if (in_array($candidate, [Role::DivisionAdministrator, Role::SystemAdministrator], true)) {
                continue;
            }

            $this->assertNotContains(
                'ecdc_domains.manage',
                RolePermissionMatrix::forRole($candidate),
                "[{$candidate->value}] must not manage the ECDC domain library."
            );
        }
    }

    private function createDomain(string $domain): ECDCDomain
    {
        $domain = ECDCDomain::create(['domain' => $domain]);

        return $domain->refresh();
    }

    private function createCompetency(ECDCDomain $domain, string $competency = 'Competency'): ECDCCompetency
    {
        return ECDCCompetency::create([
            'domain_id' => $domain->id,
            'competency' => $competency,
        ]);
    }
}
