<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Services\Rbac\RbacService;
use App\Services\Rbac\RolePermissionMatrix;
use App\Support\UserManagementTab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    public function test_the_role_index_renders_an_empty_datatable_shell(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->get(route('roles.index'))
            ->assertOk()
            ->assertSee('id="dt_user_management"', false)
            ->assertSee('<th>Users</th>', false)
            ->assertDontSee('<th>Members</th>', false)
            ->assertSee('user_management_tab = \'roles\'', false);
    }

    public function test_a_role_can_be_created_with_a_subset_of_permissions(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'Subject Coordinator',
            'permissions' => ['classrooms.view', 'students.view'],
        ])->assertRedirect(route('roles.index'));

        $role = SpatieRole::findByName('Subject Coordinator');

        $this->assertCount(2, $role->permissions);
        $this->assertTrue($role->hasPermissionTo('classrooms.view'));
        $this->assertFalse($role->hasPermissionTo('divisions.manage'));
    }

    public function test_creating_a_role_requires_a_unique_name(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->post(route('roles.store'), [
            'name' => Role::Teacher->value,
        ])->assertSessionHasErrors('name');
    }

    public function test_a_role_permission_matrix_can_be_replaced(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $role = SpatieRole::findByName(Role::Teacher->value);

        $this->actingAs($admin)->put(route('roles.update', $role), [
            'name' => $role->name,
            'permissions' => ['classrooms.view'],
        ])->assertRedirect(route('roles.index'));

        $role->refresh()->load('permissions');

        $this->assertSame(['classrooms.view'], $role->permissions->pluck('name')->all());
    }

    public function test_renaming_a_role_keeps_its_permissions(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $role = SpatieRole::findByName(Role::Teacher->value);
        $before = $role->permissions->pluck('name')->sort()->values()->all();

        $this->actingAs($admin)->put(route('roles.update', $role), [
            'name' => 'Teaching Staff',
            'permissions' => $before,
        ])->assertRedirect(route('roles.index'));

        $renamed = SpatieRole::findByName('Teaching Staff');

        $this->assertSame($before, $renamed->permissions->pluck('name')->sort()->values()->all());
    }

    public function test_a_role_can_be_deleted(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $role = SpatieRole::create(['name' => 'Temporary', 'guard_name' => 'web']);

        $this->actingAs($admin)->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'));

        $this->assertFalse(
            SpatieRole::where('name', 'Temporary')->exists(),
            'The deleted role should no longer exist.'
        );
    }

    public function test_the_system_administrator_role_cannot_be_deleted(): void
    {
        $admin = $this->userWithRole(Role::SystemAdministrator);
        $role = SpatieRole::findByName(Role::SystemAdministrator->value);

        $this->actingAs($admin)->delete(route('roles.destroy', $role))
            ->assertSessionHasErrors('error');

        $this->assertNotNull(SpatieRole::findByName(Role::SystemAdministrator->value));
    }

    public function test_a_teacher_cannot_reach_the_role_screens(): void
    {
        $teacher = $this->userWithRole(Role::Teacher);

        $this->actingAs($teacher)->get(route('roles.index'))->assertRedirect('/forbidden');
    }

    public function test_the_permission_catalogue_lists_the_whole_matrix(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->get(route('permissions.index'))
            ->assertOk()
            ->assertSee('id="dt_user_management"', false)
            ->assertSee('user_management_tab = \'permissions\'', false);
    }

    public function test_the_permission_data_endpoint_returns_paginated_rows(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $response = $this->actingAs($admin)
            ->getJson(route('user-management.data', ['tab' => 'permissions', 'draw' => 1, 'start' => 0, 'length' => 5]));

        $response->assertOk()->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertGreaterThan(0, $response->json('recordsTotal'));
        $this->assertLessThanOrEqual(5, count($response->json('data')));
    }

    public function test_the_permission_data_endpoint_filters_by_search_term(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        Permission::create(['name' => 'zzz.uniqueprobe', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->getJson(route('user-management.data', [
            'tab' => 'permissions', 'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => 'zzz.uniqueprobe'],
        ]));

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame('zzz.uniqueprobe', $response->json('data.0.name'));
    }

    public function test_the_roles_data_endpoint_paginates_and_searches(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $all = $this->actingAs($admin)->getJson(route('user-management.data', [
            'tab' => 'roles', 'draw' => 1, 'start' => 0, 'length' => 3,
        ]));
        $all->assertOk();
        $this->assertGreaterThan(3, $all->json('recordsTotal'));
        $this->assertCount(3, $all->json('data'));

        $filtered = $this->actingAs($admin)->getJson(route('user-management.data', [
            'tab' => 'roles', 'draw' => 2, 'start' => 0, 'length' => 10,
            'search' => ['value' => Role::Teacher->value],
        ]));
        $filtered->assertOk();
        $this->assertSame(1, $filtered->json('recordsFiltered'));
        $this->assertSame(Role::Teacher->value, $filtered->json('data.0.name'));
    }

    public function test_the_users_data_endpoint_searches_and_returns_action_buttons(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $target = $this->createUser(['username' => 'probe.user@deped.gov.ph']);
        app(RbacService::class)->assignRoles($target, [Role::SchoolHead->value]);

        $response = $this->actingAs($admin)->getJson(route('user-management.data', [
            'tab' => 'users', 'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => 'probe.user@'],
        ]));

        $response->assertOk();

        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame('probe.user@deped.gov.ph', $response->json('data.0.username'));
        $this->assertStringContainsString('btn-roles', $response->json('data.0.action'));
        $this->assertStringContainsString(Role::SchoolHead->value, $response->json('data.0.roles'));
    }

    public function test_the_data_endpoint_rejects_a_tab_the_role_cannot_read(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        // Grant only the roles view by attaching a role without the others.
        $limited = $this->createUser();
        app(RbacService::class)->assignRoles($limited, [Role::SchoolHead->value]);
        $limited->givePermissionTo(Permission::findByName('roles.view'));

        $this->actingAs($limited)->getJson(route('user-management.data', ['tab' => 'permissions']))
            ->assertForbidden();

        $this->actingAs($admin)->getJson(route('user-management.data', ['tab' => 'permissions']))
            ->assertOk();
    }

    public function test_the_data_endpoint_rejects_an_unknown_tab(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->getJson(route('user-management.data', ['tab' => 'nope']))
            ->assertForbidden();
    }

    public function test_the_data_endpoint_requires_authentication(): void
    {
        $this->getJson(route('user-management.data', ['tab' => 'roles']))
            ->assertRedirect('/login');
    }

    public function test_a_user_can_hold_several_roles_at_once(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $target = $this->createUser();

        $this->actingAs($admin)->put(route('users.roles.update', $target), [
            'roles' => [Role::Teacher->value, Role::SchoolHead->value],
        ])->assertRedirect(route('users.index'));

        $this->assertEqualsCanonicalizing(
            [Role::Teacher->value, Role::SchoolHead->value],
            $target->fresh()->roles->pluck('name')->all()
        );
    }

    public function test_assigning_multiple_roles_unions_their_permissions(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $target = $this->createUser();

        $this->actingAs($admin)->put(route('users.roles.update', $target), [
            'roles' => [Role::Teacher->value, Role::DepartmentHead->value],
        ]);

        $target->refresh();

        // A teacher has no classroom management, a department head does.
        $this->assertTrue($target->hasPermissionTo('term_exams.view'));
        $this->assertTrue($target->hasPermissionTo('classrooms.manage'));
    }

    public function test_assigning_roles_replaces_the_previous_set(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $target = $this->createUser();

        $this->actingAs($admin)->put(route('users.roles.update', $target), [
            'roles' => [Role::Teacher->value, Role::SchoolHead->value],
        ]);

        $this->actingAs($admin)->put(route('users.roles.update', $target), [
            'roles' => [Role::DepartmentHead->value],
        ]);

        $this->assertSame(
            [Role::DepartmentHead->value],
            $target->fresh()->roles->pluck('name')->all()
        );
    }

    public function test_manually_assigned_roles_survive_a_later_user_save(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $target = $this->userWithRole(Role::Teacher);

        $this->actingAs($admin)->put(route('users.roles.update', $target), [
            'roles' => [Role::Teacher->value, Role::SchoolHead->value],
        ]);

        // Any unrelated save must not wipe the second role.
        $target->refresh()->forceFill(['status' => true])->save();

        $this->assertEqualsCanonicalizing(
            [Role::Teacher->value, Role::SchoolHead->value],
            $target->fresh()->roles->pluck('name')->all()
        );
    }

    public function test_every_role_can_be_detached(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $target = $this->userWithRole(Role::Teacher);

        $this->actingAs($admin)->delete(route('users.roles.destroy', $target));

        $this->assertCount(0, $target->fresh()->roles);
    }

    public function test_role_assignment_rejects_an_unknown_role_name(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $target = $this->createUser();

        $this->actingAs($admin)->put(route('users.roles.update', $target), [
            'roles' => ['Warlord'],
        ])->assertSessionHasErrors('roles.0');

        $this->assertCount(0, $target->fresh()->roles);
    }

    public function test_role_assignment_requires_at_least_one_role(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $target = $this->createUser();

        $this->actingAs($admin)->put(route('users.roles.update', $target), [])
            ->assertSessionHasErrors('roles');
    }

    public function test_the_division_administrator_may_manage_roles(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->assertTrue($admin->can('roles.view'));
        $this->assertTrue($admin->can('roles.manage'));
        $this->assertTrue($admin->can('roles.assign'));
        $this->assertTrue($admin->can('permissions.manage'));
    }

    public function test_the_matrix_provisions_every_new_module_permission(): void
    {
        $permissions = ['roles.view', 'roles.manage', 'roles.assign', 'permissions.view'];

        foreach ($permissions as $permission) {
            $this->assertContains($permission, RolePermissionMatrix::all());
            $this->assertNotNull(Permission::findByName($permission));
        }
    }

    public function test_the_role_assignment_modal_endpoint_returns_roles_and_current_selection(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $target = $this->userWithRole(Role::Teacher);

        $this->actingAs($admin)
            ->put(route('users.roles.update', $target), [
                'roles' => [Role::Teacher->value, Role::SchoolHead->value],
            ]);

        $response = $this->actingAs($admin)->getJson(route('users.roles.show', $target));

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'username', 'name'], 'roles', 'assigned'])
            ->assertJsonPath('user.username', $target->username);

        $this->assertEqualsCanonicalizing(
            [Role::SchoolHead->value, Role::Teacher->value],
            $response->json('assigned')
        );
    }

    public function test_the_role_assignment_modal_is_hidden_without_the_permission(): void
    {
        $html = $this->actingAs($this->userWithRole(Role::Teacher))
            ->view('users.roles', ['user' => $this->createUser()])
            ->__toString();

        $this->assertStringNotContainsString('roles_modal', $html);
    }

    public function test_the_users_list_opens_the_roles_modal_instead_of_a_page(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $html = $this->actingAs($admin)
            ->view('users.roles', ['user' => $this->createUser()])
            ->__toString();

        $this->assertStringContainsString('id="roles_modal"', $html);
        $this->assertStringContainsString('name="roles[]"', $html);
        $this->assertStringContainsString('id="roles_select"', $html);
    }

    public function test_the_role_create_page_renders_without_a_role_record(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->get(route('roles.create'))
            ->assertOk()
            ->assertSee('Role Name', false);
    }

    public function test_the_role_edit_page_pre_checks_the_current_permissions(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $role = SpatieRole::findByName(Role::Teacher->value);

        $response = $this->actingAs($admin)->get(route('roles.edit', $role));

        $response->assertOk();
        // The role's own permissions must come back checked.
        $this->assertStringContainsString('value="classrooms.view"', $response->getContent());
        $this->assertStringContainsString(Role::Teacher->value, $response->getContent());
    }

    public function test_the_permission_tab_offers_the_add_button(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->get(route('permissions.index'))
            ->assertOk()
            ->assertSee(route('permissions.create'), false);
    }

    public function test_the_permission_create_and_edit_forms_render(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $permission = Permission::findByName('classrooms.view');

        $this->actingAs($admin)->get(route('permissions.create'))
            ->assertOk()
            ->assertSee('name="name"', false);

        $this->actingAs($admin)->get(route('permissions.edit', $permission))
            ->assertOk()
            ->assertSee('classrooms.view', false);
    }

    public function test_a_permission_can_be_created(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->post(route('permissions.store'), [
            'name' => 'customs.approve',
        ])->assertRedirect(route('permissions.index'));

        $this->assertNotNull(Permission::findByName('customs.approve'));
    }

    public function test_creating_a_permission_requires_a_unique_name(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $this->actingAs($admin)->post(route('permissions.store'), [
            'name' => 'classrooms.view',
        ])->assertSessionHasErrors('name');
    }

    public function test_a_permission_can_be_renamed(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $permission = Permission::findByName('classrooms.view');

        $this->actingAs($admin)->put(route('permissions.update', $permission), [
            'name' => 'classrooms.read',
        ])->assertRedirect(route('permissions.index'));

        $this->assertNotNull(Permission::findByName('classrooms.read'));
        $this->assertFalse(Permission::where('name', 'classrooms.view')->exists());
    }

    public function test_a_permission_can_be_deleted_and_detaches_from_roles(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);
        $permission = Permission::create(['name' => 'temp.revoke', 'guard_name' => 'web']);
        $role = SpatieRole::findByName(Role::Teacher->value);
        $role->givePermissionTo($permission);

        $this->actingAs($admin)->delete(route('permissions.destroy', $permission))
            ->assertRedirect(route('permissions.index'));

        $this->assertFalse(Permission::where('name', 'temp.revoke')->exists());

        // hasPermissionTo() throws for an unknown name, so assert on the
        // detached pivot rather than on a negative permission check.
        $this->assertFalse(
            $role->fresh()->permissions()->where('name', 'temp.revoke')->exists()
        );
    }

    public function test_a_teacher_cannot_manage_permissions(): void
    {
        $teacher = $this->userWithRole(Role::Teacher);

        // Non JSON requests are redirected to the forbidden screen.
        $this->actingAs($teacher)->post(route('permissions.store'), [
            'name' => 'sneaky.grant',
        ])->assertRedirect('/forbidden');

        $this->assertFalse(Permission::where('name', 'sneaky.grant')->exists());
    }

    public function test_the_user_management_tabs_list_every_area_for_an_administrator(): void
    {
        $admin = $this->userWithRole(Role::DivisionAdministrator);

        $labels = array_map(
            fn (UserManagementTab $tab): string => $tab->label(),
            UserManagementTab::availableFor($admin)
        );

        $this->assertSame(['User Accounts', 'Roles', 'Permissions'], $labels);
    }

    public function test_the_user_management_tabs_hide_areas_the_role_cannot_manage(): void
    {
        $labels = array_map(
            fn (UserManagementTab $tab): string => $tab->label(),
            UserManagementTab::availableFor($this->userWithRole(Role::Teacher))
        );

        $this->assertSame([], $labels);
    }

    public function test_the_tabs_render_on_each_user_management_screen(): void
    {
        $admin = $this->actingAs($this->userWithRole(Role::DivisionAdministrator));

        foreach (['/users', '/roles', '/permissions'] as $url) {
            $html = $admin->view('layouts.user-management-tabs')->__toString();

            $this->assertStringContainsString('href="/users"', $html, "[{$url}] tabs");
            $this->assertStringContainsString('href="/roles"', $html, "[{$url}] tabs");
            $this->assertStringContainsString('href="/permissions"', $html, "[{$url}] tabs");
        }
    }

    public function test_the_sidebar_no_longer_links_roles_or_permissions_separately(): void
    {
        $html = $this->actingAs($this->userWithRole(Role::DivisionAdministrator))
            ->view('layouts.sidebar', [
                'page' => ['name' => 'Dashboard'],
                'classification' => Role::DivisionAdministrator->value,
            ])->__toString();

        // User management owns the areas through tabs now.
        $this->assertStringContainsString('href="/users"', $html);
        $this->assertStringNotContainsString('href="/roles"', $html);
        $this->assertStringNotContainsString('href="/permissions"', $html);
    }
}
