<?php

namespace App\Services\Rbac;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates/refreshes the spatie roles & permissions from RolePermissionMatrix
 * and keeps each user's role in sync with tbl_users.classification.
 */
final class RbacService
{
    /**
     * Idempotently creates every permission and role, then re-grants the
     * permissions declared in the matrix.
     *
     * @return array{permissions: int, roles: int}
     */
    public function provision(): array
    {
        $this->forgetCache();

        $permissions = [];

        foreach (RolePermissionMatrix::all() as $name) {
            $permissions[$name] = Permission::findOrCreate($name, 'web');
        }

        $roles = 0;

        foreach (Role::cases() as $role) {
            $spatieRole = SpatieRole::findOrCreate($role->value, 'web');
            $spatieRole->syncPermissions(RolePermissionMatrix::forRole($role));
            $roles++;
        }

        $this->forgetCache();

        return ['permissions' => count($permissions), 'roles' => $roles];
    }

    /**
     * Mirrors the given user's classification onto its spatie roles.
     *
     * The classification role is granted additively so a user may hold several
     * roles at once. The previous implementation called syncRoles(), which
     * replaced the whole role set and silently discarded any role that had been
     * assigned manually through the role management screens.
     */
    public function syncUser(User $user): void
    {
        $role = Role::fromClassification($user->classification);

        if ($role === null) {
            $this->removeClassificationRole($user);

            return;
        }

        $spatieRole = SpatieRole::findOrCreate($role->value, 'web');

        if ($user->roles->contains('id', $spatieRole->id)) {
            return;
        }

        $user->assignRole($spatieRole);
    }

    /**
     * Replaces a user's roles with the given set.
     *
     * @param  list<string>  $roleNames
     */
    public function assignRoles(User $user, array $roleNames): void
    {
        $roles = collect($roleNames)
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name): SpatieRole => SpatieRole::findOrCreate($name, 'web'))
            ->values();

        $user->syncRoles($roles);

        $this->forgetCache();
    }

    /**
     * Drops only the role that the classification maps to, leaving any
     * additionally assigned roles in place.
     */
    private function removeClassificationRole(User $user): void
    {
        $role = Role::fromClassification($user->classification);

        if ($role !== null) {
            return;
        }

        $classificationRoles = collect(Role::names())
            ->map(fn (string $name): ?int => SpatieRole::where('name', $name)->value('id'))
            ->filter()
            ->all();

        $user->roles()
            ->whereIn('id', $classificationRoles)
            ->each(fn (SpatieRole $assigned): mixed => $user->removeRole($assigned));

        $this->forgetCache();
    }

    /**
     * Mirrors the classification of every persisted user onto spatie roles.
     *
     * @return array{synced: int, unmatched: list<string>}
     */
    public function syncAllUsers(): array
    {
        $synced = 0;
        $unmatched = [];

        User::query()
            ->select(['id', 'username', 'classification'])
            ->chunkById(500, function ($users) use (&$synced, &$unmatched): void {
                foreach ($users as $user) {
                    if (Role::fromClassification($user->classification) === null) {
                        if ($user->classification) {
                            $unmatched[] = $user->classification;
                        }

                        continue;
                    }

                    $this->syncUser($user);
                    $synced++;
                }
            });

        return ['synced' => $synced, 'unmatched' => array_values(array_unique($unmatched))];
    }

    /**
     * Registers a user role for classifications that have no role record yet.
     *
     * @return array{created: list<string>}
     */
    public function provisionMissingRoles(): array
    {
        $this->forgetCache();

        $created = [];

        foreach (Role::names() as $name) {
            if (SpatieRole::where('name', $name)->exists()) {
                continue;
            }

            SpatieRole::create(['name' => $name, 'guard_name' => 'web']);
            $created[] = $name;
        }

        $this->forgetCache();

        return ['created' => $created];
    }

    private function forgetCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Flushes the spatie permission cache (used after bulk assignments).
     */
    public function flushCache(): void
    {
        Artisan::call('permission:cache-reset');
    }
}
