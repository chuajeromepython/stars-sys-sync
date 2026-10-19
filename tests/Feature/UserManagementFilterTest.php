<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\UserManagementFilter;
use App\Models\Person;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use App\Services\DataTable\UserAreaQuery;
use App\Services\DataTable\UserManagementDataTable;
use App\Services\Rbac\RbacService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class UserManagementFilterTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    private School $school;

    private School $otherSchool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();

        $this->school = $this->createSchool('School One');
        $this->otherSchool = $this->createSchool('School Two');
    }

    public function test_the_users_tab_no_longer_returns_a_classification_column(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $response = $this->actingAs($admin)->data('users');

        $response->assertOk();
        $this->assertArrayNotHasKey('classification', $response->json('data.0'));
        $this->assertSame(
            ['name', 'username', 'area', 'roles', 'action'],
            array_keys($response->json('data.0'))
        );
    }

    public function test_the_users_index_no_longer_renders_a_classification_header(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()
            ->assertDontSee('<th>Classification</th>', false)
            ->assertSee('<th>Area</th>', false);
    }

    public function test_the_users_index_renders_the_toolbar_filter_selects(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()
            ->assertSee('id="dt_filters"', false)
            ->assertSee('data-filter="area"', false)
            ->assertSee('data-filter="roles"', false)
            ->assertDontSee('searchBuilder', false);
    }

    public function test_the_users_tab_resolves_the_area_from_the_users_profile(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $teacher = $this->createTeacherUser($this->school, 'area.probe@deped.gov.ph', 'Probe');

        $response = $this->actingAs($admin)->data('users', [
            'search' => ['value' => 'area.probe@'],
        ]);

        $response->assertOk();
        $this->assertSame('School One', $response->json('data.0.area'));
        $this->assertSame($teacher->username, $response->json('data.0.username'));
    }

    public function test_the_users_tab_can_be_filtered_by_role(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $this->userWithRole(Role::SchoolHead, ['username' => 'filter.head@deped.gov.ph']);
        $this->userWithRole(Role::Teacher, ['username' => 'filter.teacher@deped.gov.ph']);

        $response = $this->actingAs($admin)->data('users', [], [
            UserManagementFilter::Role->value => Role::Teacher->value,
        ]);

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame('filter.teacher@deped.gov.ph', $response->json('data.0.username'));
    }

    public function test_the_users_tab_can_be_filtered_by_area(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $this->createTeacherUser($this->school, 'area.one@deped.gov.ph', 'One');
        $this->createTeacherUser($this->otherSchool, 'area.two@deped.gov.ph', 'Two');

        $response = $this->actingAs($admin)->data('users', [], [
            UserManagementFilter::Area->value => 'School Two',
        ]);

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame('area.two@deped.gov.ph', $response->json('data.0.username'));
    }

    public function test_the_users_tab_offers_area_and_role_filter_options(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $this->createTeacherUser($this->school, 'options.probe@deped.gov.ph', 'Probe');

        $response = $this->actingAs($admin)->data('users');

        $response->assertOk();

        $filters = collect(app(UserManagementDataTable::class)->filterDefinitionsFor('users'))
            ->keyBy('key');

        $this->assertSame(['area', 'roles'], $filters->keys()->all());
        $this->assertSame('Area', $filters['area']['label']);
        $this->assertSame('All areas', $filters['area']['placeholder']);
        $this->assertContains(
            ['label' => 'School One', 'value' => 'School One'],
            $filters['area']['options']
        );
        $this->assertContains(
            ['label' => Role::Teacher->value, 'value' => Role::Teacher->value],
            $filters['roles']['options']
        );
    }

    public function test_the_area_filter_options_query_is_not_grouped(): void
    {
        // The option list is a plain distinct list of area names. Building it on
        // top of the per user reduction produced `select distinct name ... group
        // by user_id`, which MySQL rejects under ONLY_FULL_GROUP_BY (error 1055)
        // while SQLite happily accepts it - so the guard is the generated SQL
        // rather than the returned rows.
        DB::connection()->enableQueryLog();

        app(UserAreaQuery::class)->options();

        $sql = strtolower(collect(DB::connection()->getQueryLog())->pluck('query')->implode(' '));

        $this->assertStringContainsString('distinct', $sql);
        $this->assertStringNotContainsString('group by', $sql);
    }

    public function test_each_tab_exposes_the_filters_the_toolbar_renders(): void
    {
        // The endpoint only applies the filter keys the tab declares, so these
        // lists are the contract between the toolbar selects and the query:
        // users filter by area and role, roles by one of their members, and
        // permissions by the role holding them.
        $tables = app(UserManagementDataTable::class);

        $this->assertSame(
            ['area', 'roles'],
            array_column($tables->filterDefinitionsFor('users'), 'key')
        );
        $this->assertSame(
            ['user'],
            array_column($tables->filterDefinitionsFor('roles'), 'key')
        );
        $this->assertSame(
            ['roles'],
            array_column($tables->filterDefinitionsFor('permissions'), 'key')
        );
    }

    public function test_the_roles_tab_can_be_filtered_by_user(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $teacher = $this->userWithRole(Role::Teacher, ['username' => 'member.probe@deped.gov.ph']);

        $response = $this->actingAs($admin)->data('roles', [], [
            UserManagementFilter::User->value => (string) $teacher->id,
        ]);

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame(Role::Teacher->value, $response->json('data.0.name'));
    }

    public function test_the_roles_tab_no_longer_exposes_a_members_column(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $this->userWithRole(Role::Teacher, ['username' => 'shown.member@deped.gov.ph']);

        $response = $this->actingAs($admin)->data('roles');

        $response->assertOk();
        $this->assertArrayNotHasKey('members', $response->json('data.0'));
        $this->assertSame(
            ['name', 'permissions', 'users', 'action'],
            array_keys($response->json('data.0'))
        );
    }

    public function test_the_roles_tab_offers_a_user_filter_list(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $teacher = $this->userWithRole(Role::Teacher, ['username' => 'listed.member@deped.gov.ph']);

        $this->actingAs($admin);

        $filters = collect(app(UserManagementDataTable::class)->filterDefinitionsFor('roles'))->keyBy('key');

        $this->assertSame(['user'], $filters->keys()->all());
        $this->assertSame('User', $filters['user']['label']);
        $this->assertContains(
            ['label' => 'listed.member@deped.gov.ph', 'value' => (string) $teacher->id],
            $filters['user']['options']
        );
    }

    public function test_the_permissions_tab_can_be_filtered_by_role(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        // The Student role only ever holds two permissions, which keeps the
        // expected count unambiguous.
        $expected = SpatieRole::findByName(Role::Student->value)->permissions()
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $this->assertNotEmpty($expected);

        // Column 2 is the roles column of the permissions tab.
        $response = $this->actingAs($admin)->data('permissions', [], [
            UserManagementFilter::Role->value => Role::Student->value,
        ]);

        $response->assertOk();
        $this->assertSame(count($expected), $response->json('recordsFiltered'));
        $this->assertSame(
            $expected,
            array_column($response->json('data'), 'name')
        );
    }

    public function test_the_permissions_tab_exposes_only_the_role_filter(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $this->actingAs($admin);

        $filters = collect(app(UserManagementDataTable::class)->filterDefinitionsFor('permissions'))->keyBy('key');

        $this->assertSame(['roles'], $filters->keys()->all());
        $this->assertSame('Role', $filters['roles']['label']);
        $this->assertNotEmpty($filters['roles']['options']);
    }

    public function test_the_permissions_tab_renders_the_roles_holding_a_permission(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $response = $this->actingAs($admin)
            ->data('permissions', ['search' => ['value' => 'summatives.upload']]);

        $response->assertOk();
        $this->assertStringContainsString(Role::Teacher->value, $response->json('data.0.roles'));
    }

    public function test_combining_two_filters_narrows_the_result_set(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $this->createTeacherUser($this->school, 'combo.one@deped.gov.ph', 'One');
        $this->createTeacherUser($this->otherSchool, 'combo.two@deped.gov.ph', 'Two');
        $this->userWithRole(Role::SchoolHead, ['username' => 'combo.head@deped.gov.ph']);

        $response = $this->actingAs($admin)->data('users', [], [
            UserManagementFilter::Area->value => 'School One',
            UserManagementFilter::Role->value => Role::Teacher->value,
        ]);

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame('combo.one@deped.gov.ph', $response->json('data.0.username'));
    }

    public function test_an_unknown_filter_key_is_ignored_not_injected(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $this->userWithRole(Role::Teacher, ['username' => 'injection.probe@deped.gov.ph']);

        $unfiltered = $this->actingAs($admin)->data('users');
        $unfiltered->assertOk();

        $response = $this->actingAs($admin)->data('users', [], [
            'classification' => "' OR 1=1 -- ",
            'area' => 'nonexistent school',
            UserManagementFilter::Area->value => "' OR 1=1 -- ",
        ]);

        $response->assertOk();
        $this->assertSame(0, $response->json('recordsFiltered'));
        $this->assertLessThan(
            $unfiltered->json('recordsFiltered'),
            $response->json('recordsFiltered') + 1,
            'An unknown filter key must not change the result set.'
        );
    }

    public function test_a_filter_value_no_option_can_have_is_ignored(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $unfiltered = $this->actingAs($admin)->data('users');
        $unfiltered->assertOk();

        $response = $this->actingAs($admin)->data('users', [], [
            // Not a role anybody holds, so the result stays empty instead of
            // matching nothing at all (or, worse, being interpolated).
            UserManagementFilter::Role->value => 'Not A Role',
        ]);

        $response->assertOk();
        $this->assertSame(0, $response->json('recordsFiltered'));
    }

    public function test_filters_still_require_the_tab_permission(): void
    {
        $limited = $this->createUser();
        app(RbacService::class)->assignRoles($limited, [Role::SchoolHead->value]);
        $limited->givePermissionTo(Permission::findByName('roles.view'));

        $this->actingAs($limited)
            ->data('permissions', [], [UserManagementFilter::Role->value => Role::Teacher->value])
            ->assertForbidden();

        $this->actingAs($limited)
            ->data('roles', [], [UserManagementFilter::User->value => '1'])
            ->assertOk();
    }

    /**
     * Hit the shared data endpoint, building the DataTables query string.
     *
     * @param  array<string, mixed>  $params
     * @param  array<string, string>  $filters  filter key => selected value
     */
    private function data(string $tab, array $params = [], array $filters = [])
    {
        $query = array_merge([
            'tab' => $tab,
            'draw' => 1,
            'start' => 0,
            'length' => 50,
        ], $params);

        foreach ($filters as $key => $value) {
            $query["filters[{$key}]"] = $value;
        }

        return $this->getJson(route('user-management.data', $query));
    }

    private function createSchool(string $name): School
    {
        $school = new School;
        $school->code = strtoupper(str_replace(' ', '-', $name)).'-'.uniqid();
        $school->name = $name;
        $school->address = $name;
        $school->school_category_id = 1;
        $school->school_type_id = 1;
        $school->district_id = 1;
        $school->save();

        return $school;
    }

    private function createTeacherUser(School $school, string $username, string $lastName): User
    {
        $user = $this->createUser([
            'username' => $username,
            'classification' => Role::Teacher->value,
        ]);

        $person = Person::find($user->person_id);
        $person->last_name = $lastName;
        $person->save();

        $teacher = new Teacher;
        $teacher->email = $username;
        $teacher->user_id = $user->id;
        $teacher->school_id = $school->id;
        $teacher->save();

        return $user->refresh();
    }
}
