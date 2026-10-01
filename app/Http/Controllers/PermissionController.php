<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Services\DataTable\UserManagementDataTable;
use App\Services\Rbac\RbacService;
use App\Services\Rbac\RolePermissionMatrix;
use App\Support\UserManagementTab;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(
        private RbacService $rbac,
        private UserManagementDataTable $dataTable,
    ) {}

    public function index(): View
    {
        $page = [
            'name' => 'Permission',
            'title' => 'Permission Management',
            'crumb' => ['Users' => '/users', 'Permissions' => '/permissions'],
        ];

        $missing = array_values(array_filter(
            RolePermissionMatrix::all(),
            fn (string $permission): bool => ! Permission::where('name', $permission)->exists()
        ));

        $filters = $this->dataTable->filterDefinitionsFor(UserManagementTab::Permissions->key());

        return view('permissions.index', compact('page', 'missing', 'filters'));
    }

    public function create(): View
    {
        $page = [
            'name' => 'Permission',
            'title' => 'Add Permission',
            'crumb' => ['Users' => '/users', 'Permissions' => '/permissions', 'Add Permission' => '/permissions/create'],
        ];

        return view('permissions.create', compact('page'));
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $permission = Permission::create([
            'name' => $request->validated('name'),
            'guard_name' => $request->validated('guard_name') ?: 'web',
        ]);

        $this->rbac->flushCache();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission "'.$permission->name.'" has been created successfully.');
    }

    public function edit(Permission $permission): View
    {
        $page = [
            'name' => 'Permission',
            'title' => 'Edit Permission',
            'crumb' => ['Users' => '/users', 'Permissions' => '/permissions', 'Edit Permission' => ''],
        ];

        return view('permissions.edit', compact('page', 'permission'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->update([
            'name' => $request->validated('name'),
            'guard_name' => $request->validated('guard_name') ?: $permission->guard_name,
        ]);

        $this->rbac->flushCache();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission "'.$permission->name.'" has been updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $name = $permission->name;
        $permission->delete();

        $this->rbac->flushCache();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission "'.$name.'" has been deleted successfully.');
    }
}
