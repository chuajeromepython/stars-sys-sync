<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use App\Services\Rbac\RbacService;
use App\Services\Rbac\RolePermissionMatrix;
use App\Support\AssessmentTab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    public function test_it_creates_every_role_and_permission_from_the_matrix(): void
    {
        $this->assertSame(count(Role::cases()), SpatieRole::count());
        $this->assertSame(count(RolePermissionMatrix::all()), Permission::count());

        foreach (RolePermissionMatrix::all() as $name) {
            $this->assertNotNull(Permission::findByName($name));
        }
    }

    public function test_the_system_administrator_receives_every_permission(): void
    {
        $admin = SpatieRole::findByName(Role::SystemAdministrator->value);

        $this->assertCount(count(RolePermissionMatrix::all()), $admin->permissions);
    }

    public function test_a_role_only_receives_the_permissions_declared_for_it(): void
    {
        $teacher = SpatieRole::findByName(Role::Teacher->value);

        $this->assertTrue($teacher->hasPermissionTo('assessments.upload'));
        $this->assertFalse($teacher->hasPermissionTo('users.view'));
        $this->assertFalse($teacher->hasPermissionTo('divisions.manage'));
    }

    public function test_saving_a_user_mirrors_the_classification_onto_a_role(): void
    {
        $user = $this->userWithRole(Role::SchoolHead);

        $this->assertTrue($user->hasRole(Role::SchoolHead->value));
        $this->assertFalse($user->hasRole(Role::Teacher->value));
    }

    public function test_changing_the_classification_adds_the_matching_role(): void
    {
        $user = $this->userWithRole(Role::Teacher);

        $user->classification = Role::DivisionAdministrator->value;
        $user->save();

        // The classification role is granted additively so a user can hold
        // several roles at once; the previous role is not revoked here.
        $this->assertTrue($user->fresh()->hasRole(Role::DivisionAdministrator->value));
        $this->assertTrue($user->fresh()->hasRole(Role::Teacher->value));
    }

    public function test_an_unknown_classification_is_left_without_a_role(): void
    {
        $user = $this->userWithRole(Role::Teacher);

        $user->classification = 'Retired Personnel';
        $user->save();

        $this->assertCount(0, $user->fresh()->roles);
    }

    public function test_sync_all_users_reports_unmatched_classifications(): void
    {
        $this->userWithRole(Role::Teacher);
        $this->userWithRole(Role::Teacher, ['username' => 'legacy@deped.gov.ph']);
        User::where('username', 'legacy@deped.gov.ph')->update(['classification' => 'Legacy Officer']);

        $result = app(RbacService::class)->syncAllUsers();

        $this->assertSame(1, $result['synced']);
        $this->assertSame(['Legacy Officer'], $result['unmatched']);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/teachers')->assertRedirect('/login');
    }

    public function test_an_authenticated_user_without_the_permission_is_redirected_to_forbidden(): void
    {
        $teacher = $this->userWithRole(Role::Teacher);

        $this->actingAs($teacher)->get('/users')->assertRedirect('/forbidden');
    }

    public function test_an_authenticated_user_with_the_permission_passes_the_middleware(): void
    {
        $schoolHead = $this->userWithRole(Role::SchoolHead);

        $response = $this->actingAs($schoolHead)->get('/users');

        $this->assertNotSame('/forbidden', $response->headers->get('Location'));
        $this->assertNotSame(403, $response->getStatusCode());
    }

    public function test_a_json_request_without_the_permission_receives_a_403(): void
    {
        $teacher = $this->userWithRole(Role::Teacher);

        $this->actingAs($teacher)->getJson('/teachers/data')->assertForbidden();
    }

    public function test_the_sidebar_only_renders_links_the_role_may_access(): void
    {
        $teacher = $this->actingAs($this->userWithRole(Role::Teacher));

        $html = $teacher->view('layouts.sidebar', [
            'page' => ['name' => 'Dashboard'],
            'classification' => Role::Teacher->value,
        ])->__toString();

        $this->assertStringContainsString('href="/item_banks"', $html);
        $this->assertStringContainsString('href="/term-exams"', $html);
        $this->assertStringNotContainsString('href="/users"', $html);
        $this->assertStringNotContainsString('href="/divisions"', $html);
        $this->assertStringNotContainsString('href="/trails"', $html);

        $schoolHead = $this->actingAs($this->userWithRole(Role::SchoolHead));

        $html = $schoolHead->view('layouts.sidebar', [
            'page' => ['name' => 'Dashboard'],
            'classification' => Role::SchoolHead->value,
        ])->__toString();

        $this->assertStringContainsString('href="/department_heads"', $html);
        $this->assertStringNotContainsString('href="/term-exams"', $html);
        $this->assertStringNotContainsString('href="/users"', $html);
    }

    public function test_the_sidebar_lists_each_destination_once(): void
    {
        $html = $this->actingAs($this->userWithRole(Role::SchoolHead))
            ->view('layouts.sidebar', [
                'page' => ['name' => 'Dashboard'],
                'classification' => Role::SchoolHead->value,
            ])->__toString();

        foreach (['/competencies', '/teachers', '/students', '/sections', '/classrooms'] as $url) {
            $this->assertSame(1, substr_count($html, 'href="'.$url.'"'), "[$url] should appear exactly once.");
        }
    }

    public function test_the_assessment_entry_points_at_the_first_permitted_tab(): void
    {
        $teacher = $this->actingAs($this->userWithRole(Role::Teacher));

        $html = $teacher->view('layouts.sidebar', [
            'page' => ['name' => 'Dashboard'],
            'classification' => Role::Teacher->value,
        ])->__toString();

        $this->assertStringContainsString('href="/term-exams"', $html);
        $this->assertStringNotContainsString('href="/diagnostics"', $html);
        $this->assertStringNotContainsString('href="/summatives"', $html);
        $this->assertStringNotContainsString('href="/ecdcs"', $html);
    }

    public function test_the_assessment_tabs_only_offer_permitted_areas(): void
    {
        $tabs = AssessmentTab::availableFor($this->userWithRole(Role::Teacher));
        $labels = array_map(fn (AssessmentTab $tab) => $tab->label(), $tabs);

        $this->assertSame(['Term Exam', 'Diagnostic Test', 'Summative Test', 'ECDC'], $labels);

        $tabs = AssessmentTab::availableFor($this->userWithRole(Role::SchoolHead));
        $labels = array_map(fn (AssessmentTab $tab) => $tab->label(), $tabs);

        $this->assertSame(['ECDC'], $labels);
    }

    public function test_every_permission_used_by_a_route_exists_in_the_matrix(): void
    {
        $known = RolePermissionMatrix::all();
        $used = [];

        foreach (Route::getRoutes() as $route) {
            foreach ($route->gatherMiddleware() as $middleware) {
                if (! is_string($middleware) || ! str_starts_with($middleware, 'permission:')) {
                    continue;
                }

                // A single middleware may list several permissions, either
                // comma separated (any of) or pipe separated (all of).
                foreach (preg_split('/[|,]/', Str::after($middleware, 'permission:')) as $permission) {
                    $permission = trim($permission);

                    if ($permission !== '') {
                        $used[] = $permission;
                    }
                }
            }
        }

        $this->assertNotEmpty($used);

        foreach (array_unique($used) as $permission) {
            $this->assertContains($permission, $known, "Route permission [{$permission}] is not declared in the matrix.");
        }
    }
}
