<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\DataTable\DataTablePaginator;
use App\Services\DataTable\UserManagementDataTable;
use App\Services\Rbac\RbacService;
use App\Services\Rbac\RolePermissionMatrix;
use App\Support\UserManagementTab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private RbacService $rbac,
        private UserManagementDataTable $dataTable,
        private DataTablePaginator $paginator,
    ) {}

    /**
     * Server side processed rows for one of the user management tabs.
     */
    public function data(Request $request): JsonResponse
    {
        $tab = (string) $request->query('tab', '');

        // An unknown tab resolves to a null permission, which must not be
        // treated as "allowed" the way can(null) would.
        $permission = UserManagementTab::permissionFor($tab);

        if ($permission === null || ! auth()->user()?->can($permission)) {
            abort(403);
        }
        $resolved = $this->dataTable->resolve($tab);

        if ($resolved === null) {
            return response()->json([
                'draw' => (int) $request->query('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        return response()->json($this->paginator->paginate(
            $resolved['query'],
            $request,
            $resolved['columns'],
            $resolved['filters'] ?? []
        ));
    }

    public function index(): View
    {
        $page = [
            'name' => 'Role',
            'title' => 'Role Management',
            'crumb' => ['Users' => '/users', 'Roles' => '/roles'],
        ];

        $filters = $this->dataTable->filterDefinitionsFor(UserManagementTab::Roles->key());

        return view('roles.index', compact('page', 'filters'));
    }

    public function create(): View
    {
        $page = [
            'name' => 'Role',
            'title' => 'Add Role',
            'crumb' => ['Roles' => '/roles', 'Add Role' => '/roles/create'],
        ];

        return view('roles.create', [
            'page' => $page,
            'modules' => $this->permissionsByModule(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($this->validPermissions($request));

        $this->rbac->flushCache();

        return redirect()->route('roles.index')
            ->with('success', 'Role "'.$role->name.'" has been created successfully.');
    }

    public function edit(Role $role): View
    {
        $page = [
            'name' => 'Role',
            'title' => 'Edit Role',
            'crumb' => ['Roles' => '/roles', 'Edit Role' => '/roles/'.$role->id.'/edit'],
        ];

        return view('roles.edit', [
            'page' => $page,
            'role' => $role,
            'modules' => $this->permissionsByModule(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($this->validPermissions($request));

        $this->rbac->flushCache();

        return redirect()->route('roles.index')
            ->with('success', 'Role "'.$role->name.'" has been updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === \App\Enums\Role::SystemAdministrator->value) {
            return redirect()->route('roles.index')
                ->withErrors(['error' => 'The System Administrator role cannot be deleted.']);
        }

        $name = $role->name;
        $role->delete();

        $this->rbac->flushCache();

        return redirect()->route('roles.index')
            ->with('success', 'Role "'.$name.'" has been deleted successfully.');
    }

    /**
     * The known permissions grouped by module, for the matrix checkboxes.
     *
     * @return array<string, Collection<int, string>>
     */
    private function permissionsByModule(): array
    {
        $grouped = collect(RolePermissionMatrix::all())
            ->groupBy(fn (string $permission): string => str($permission)->beforeLast('.')->toString());

        return $grouped->map(fn (Collection $permissions): Collection => $permissions->sort()->values())->all();
    }

    /**
     * Restrict the submitted permissions to those that actually exist.
     *
     * @return list<string>
     */
    private function validPermissions(Request $request): array
    {
        $submitted = (array) $request->input('permissions', []);
        $known = Permission::pluck('name')->all();

        return array_values(array_intersect($submitted, $known));
    }
}
