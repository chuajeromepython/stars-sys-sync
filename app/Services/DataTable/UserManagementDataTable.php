<?php

namespace App\Services\DataTable;

use App\Enums\Role;
use App\Enums\UserManagementFilter;
use App\Models\SchoolSupervisor;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Server side processed listings for the three user management tabs.
 */
class UserManagementDataTable
{
    /**
     * The tabs that expose a paginated listing.
     */
    public const TABS = ['users', 'roles', 'permissions'];

    public function __construct(private UserAreaQuery $areas) {}

    /**
     * The roles held by each user of the current page, memoised per request.
     *
     * @var array<int, list<string>>
     */
    private array $roleCache = [];

    /**
     * The role names held by each permission of the current page.
     *
     * @var array<int, list<string>>
     */
    private array $permissionRoleCache = [];

    /**
     * Resolve the base query, column and filter definitions for a tab.
     *
     * @return array{query: Builder, columns: array<int, array<string, mixed>>, filters: array<string, array<string, callable>>}|null
     */
    public function resolve(string $tab): ?array
    {
        return match ($tab) {
            'users' => $this->users(),
            'roles' => $this->roles(),
            'permissions' => $this->permissions(),
            default => null,
        };
    }

    /**
     * The toolbar filters of a tab, ready to be rendered as selects.
     *
     * The options are resolved here, once, so the toolbar never offers a value
     * the query behind it could not match, and a request can only ever apply a
     * filter this tab declares.
     *
     * @return list<array{key: string, label: string, placeholder: string, options: list<array{label: string, value: string}>}>
     */
    public function filterDefinitionsFor(string $tab): array
    {
        $resolved = $this->resolve($tab);

        if ($resolved === null) {
            return [];
        }

        $filters = [];

        foreach (UserManagementFilter::forTab($tab) as $filter) {
            $definition = $resolved['filters'][$filter->value] ?? null;

            if ($definition === null) {
                continue;
            }

            $filters[] = [
                'key' => $filter->value,
                'label' => $filter->label(),
                'placeholder' => $filter->placeholder(),
                'options' => ($definition['options'])(),
            ];
        }

        return $filters;
    }

    /**
     * User accounts joined to their person record, with the assigned roles.
     *
     * Reuses the classification and area scoping that the listing has always
     * applied, so a school head only ever sees the staff of their own school.
     *
     * @return array{query: Builder, columns: array<int, array<string, mixed>>, filters: array<string, array<string, callable>>}
     */
    private function users(): array
    {
        $query = User::query()
            ->select([
                'tbl_users.id as id',
                'username',
                'first_name',
                'middle_name',
                'last_name',
            ])
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('tbl_users.status', 1);

        $query = $this->areas->join($query);

        $query = $this->scopeUsers($query);

        return [
            'query' => $query,
            'columns' => [
                [
                    'data' => 'name',
                    'column' => 'last_name',
                    'render' => fn ($row, $value) => e(trim($row->last_name.', '.$row->first_name.' '.$row->middle_name)),
                ],
                [
                    'data' => 'username',
                    'column' => 'username',
                    'render' => fn ($row, $value) => e($value),
                ],
                $this->areaColumn(),
                [
                    'data' => 'roles',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row) => $this->roleBadges((int) $row->id),
                ],
                [
                    'data' => 'action',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row) => $this->userActions($row),
                ],
            ],
            'filters' => [
                UserManagementFilter::Area->value => [
                    'options' => fn (): array => $this->areas->options(),
                    'apply' => fn (Builder $query, string $value) => $query->where('area.name', $value),
                ],
                UserManagementFilter::Role->value => [
                    'options' => fn (): array => $this->roleOptions(),
                    'apply' => fn (Builder $query, string $value) => $this->filterUsersByRole($query, [$value]),
                ],
            ],
        ];
    }

    /**
     * The area column, resolved from the user's profile.
     *
     * @return array<string, mixed>
     */
    private function areaColumn(): array
    {
        return [
            'data' => 'area',
            'column' => 'area.name',
            'render' => fn ($row) => e($row->area ?: 'N/A'),
        ];
    }

    /**
     * The filter options for the users tab role filter.
     *
     * @return list<array{label: string, value: string}>
     */
    private function roleOptions(): array
    {
        return SpatieRole::orderBy('name')
            ->pluck('name')
            ->map(fn (string $name): array => ['label' => $name, 'value' => $name])
            ->all();
    }

    /**
     * Apply the encoder scoping the listing requires.
     *
     * The area filter comes in through the toolbar selects (`filters[area]`)
     * and is applied by the paginator; it is no longer read from the request
     * directly.
     */
    private function scopeUsers(Builder $query): Builder
    {
        $encoder = auth()->user()?->classification;

        // A school head is confined to their own school.
        if ($encoder === 'School Head') {
            $schoolId = SchoolSupervisor::where('user_id', auth()->id())->value('school_id');

            $query->where(function (Builder $scoped) use ($schoolId): void {
                $scoped->whereIn('classification', ['Teacher', 'Student'])
                    ->whereExists(fn ($q) => $q->from('tbl_teachers')
                        ->whereColumn('tbl_teachers.user_id', 'tbl_users.id')
                        ->where('tbl_teachers.school_id', $schoolId));
            });
        }

        return $query;
    }

    /**
     * Restrict the user listing to the given role names.
     *
     * @param  list<string>  $values
     */
    private function filterUsersByRole(Builder $query, array $values): void
    {
        $query->whereHas(
            'roles',
            fn (Builder $roles) => $roles->whereIn(
                (new SpatieRole)->getTable().'.name',
                $values
            )
        );
    }

    /**
     * Roles with their permission and user counts.
     *
     * The roles tab filters through its toolbar select (`filters[user]`), so no
     * member column participates in the listing anymore. Filtering stays on the
     * `users` relation of the role.
     *
     * @return array{query: Builder, columns: array<int, array<string, mixed>>, filters: array<string, array<string, callable>>}
     */
    private function roles(): array
    {
        $query = SpatieRole::query()
            ->select(['id', 'name'])
            ->withCount(['permissions', 'users'])
            ->orderBy('name');

        return [
            'query' => $query,
            'columns' => [
                [
                    'data' => 'name',
                    'column' => 'name',
                    'render' => fn ($row, $value) => e($value),
                ],
                [
                    'data' => 'permissions',
                    'source' => 'permissions_count',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row, $value) => $this->countPill($value, 'permissions'),
                ],
                [
                    'data' => 'users',
                    'source' => 'users_count',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row, $value) => $this->countPill($value, 'users'),
                ],
                [
                    'data' => 'action',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row) => $this->roleActions($row),
                ],
            ],
            'filters' => [
                UserManagementFilter::User->value => [
                    'options' => fn (): array => $this->userOptions(),
                    'apply' => fn (Builder $query, string $value) => $this->filterRolesByUser($query, [$value]),
                ],
            ],
        ];
    }

    /**
     * Every active user, as a filter option list.
     *
     * @return list<array{label: string, value: string}>
     */
    private function userOptions(): array
    {
        return User::query()
            ->select(['tbl_users.id', 'username'])
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('tbl_users.status', 1)
            ->orderBy('tbl_persons.last_name')
            ->get()
            ->map(fn (User $user): array => [
                'label' => $this->personLabel($user),
                'value' => (string) $user->id,
            ])
            ->all();
    }

    /**
     * Restrict the role listing to the roles held by the given user ids.
     *
     * @param  list<string>  $values
     */
    private function filterRolesByUser(Builder $query, array $values): void
    {
        $query->whereHas(
            'users',
            fn (Builder $users) => $users->whereIn('tbl_users.id', $values)
        );
    }

    /**
     * A user's display name with their username, used in the filter lists.
     */
    private function personLabel(User $user): string
    {
        $person = $user->person;

        $name = $person
            ? trim(implode(', ', array_filter([
                $person->last_name,
                $person->first_name,
                $person->middle_name,
            ])))
            : '';

        return $name === '' ? $user->username : $name.' ('.$user->username.')';
    }

    /**
     * Permissions grouped implicitly by their module prefix.
     *
     * @return array{query: Builder, columns: array<int, array<string, mixed>>, filters: array<string, array<string, callable>>}
     */
    private function permissions(): array
    {
        $query = Permission::query()
            ->select(['id', 'name'])
            ->withCount('roles')
            ->orderBy('name');

        return [
            'query' => $query,
            'columns' => [
                [
                    'data' => 'module',
                    'column' => 'name',
                    'render' => fn ($row) => $this->moduleBadge($row->name),
                ],
                [
                    'data' => 'name',
                    'column' => 'name',
                    'render' => fn ($row, $value) => e($value),
                ],
                [
                    'data' => 'roles',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row) => $this->permissionRoleBadges((int) $row->id),
                ],
                [
                    'data' => 'action',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row) => $this->permissionActions($row),
                ],
            ],
            'filters' => [
                UserManagementFilter::Role->value => [
                    'options' => fn (): array => $this->roleOptions(),
                    'apply' => fn (Builder $query, string $value) => $this->filterPermissionsByRole($query, [$value]),
                ],
            ],
        ];
    }

    /**
     * Restrict the permission listing to the given role names.
     *
     * @param  list<string>  $values
     */
    private function filterPermissionsByRole(Builder $query, array $values): void
    {
        $query->whereHas(
            'roles',
            fn (Builder $roles) => $roles->whereIn((new SpatieRole)->getTable().'.name', $values)
        );
    }

    /**
     * The roles held by the given user, as badges.
     */
    private function roleBadges(int $userId): string
    {
        $roles = $this->roleCache[$userId] ??= User::find($userId)?->roles->pluck('name')->all() ?? [];

        if ($roles === []) {
            return '<span class="badge badge-danger">No role</span>';
        }

        return collect($roles)
            ->map(fn (string $role): string => '<span class="badge badge-primary mr-1">'.e($role).'</span>')
            ->implode('');
    }

    /**
     * The roles holding a permission, as badges.
     */
    private function permissionRoleBadges(int $permissionId): string
    {
        $roles = $this->permissionRoleCache[$permissionId] ??= Permission::find($permissionId)?->roles
            ->pluck('name')
            ->sort()
            ->values()
            ->all() ?? [];

        if ($roles === []) {
            return '<span class="badge badge-danger">Not granted</span>';
        }

        return collect($roles)
            ->map(fn (string $role): string => '<span class="badge badge-primary mr-1">'.e($role).'</span>')
            ->implode('');
    }

    /**
     * A count rendered as a pill, so the role listing reads at a glance.
     */
    private function countPill(string $value, string $label): string
    {
        $count = (int) $value;

        return '<span class="badge '.($count === 0 ? 'badge-secondary' : 'badge-primary').'">'
            .$count.' '.$label.'</span>';
    }

    /**
     * The module prefix of a permission, as a chip.
     */
    private function moduleBadge(string $permission): string
    {
        $module = str($permission)->beforeLast('.')->headline()->toString();

        return '<span class="badge badge-info">'.e($module).'</span>';
    }

    /**
     * The action buttons for a user row.
     */
    private function userActions($row): string
    {
        $id = (int) $row->id;
        $username = e($row->username);
        $actions = '';

        if (auth()->user()?->can('roles.assign')) {
            $actions .= '<a href="#" class="btn btn-success btn-sm btn-roles" data-toggle="modal"'
                .' data-target="#roles_modal" data-roles_id="'.$id.'"'
                .' data-roles_username="'.$username.'">'
                .'<i class="fa fa-user-tag"></i></a> ';
        }

        return $actions
            .'<a href="/users/'.$id.'/edit" class="btn btn-primary btn-sm"><i class="fa fa-pen"></i></a> '
            .'<a href="#" class="btn btn-info btn-sm btn-reset" data-toggle="modal"'
            .' data-target="#reset_modal" data-reset_id="'.$id.'"'
            .' data-reset_username="'.$username.'"><i class="fa fa-unlock"></i></a> '
            .'<a href="#" class="btn btn-danger btn-sm btn-destroy" data-toggle="modal"'
            .' data-target="#destroy_modal" data-destroy_id="'.$id.'"'
            .' data-destroy_username="'.$username.'"><i class="fa fa-trash"></i></a>';
    }

    /**
     * The action buttons for a role row.
     */
    private function roleActions($row): string
    {
        $id = (int) $row->id;
        $actions = '<a href="'.route('roles.edit', $id).'" class="btn btn-primary btn-sm">'
            .'<i class="fa fa-pen"></i></a>';

        if (auth()->user()?->can('roles.manage') && $row->name !== Role::SystemAdministrator->value) {
            $actions .= ' <form method="post" class="d-inline" action="'.route('roles.destroy', $id).'"'
                .' onsubmit="return confirm(\'Delete the '.e($row->name).' role?\');">'
                .csrf_field().method_field('DELETE')
                .'<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>'
                .'</form>';
        }

        return $actions;
    }

    /**
     * The action buttons for a permission row.
     */
    private function permissionActions($row): string
    {
        if (! auth()->user()?->can('permissions.manage')) {
            return '';
        }

        $id = (int) $row->id;

        return '<a href="'.route('permissions.edit', $id).'" class="btn btn-primary btn-sm">'
            .'<i class="fa fa-pen"></i></a>'
            .' <form method="post" class="d-inline" action="'.route('permissions.destroy', $id).'"'
            .' onsubmit="return confirm(\'Delete the '.e($row->name).' permission?\');">'
            .csrf_field().method_field('DELETE')
            .'<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>'
            .'</form>';
    }
}
