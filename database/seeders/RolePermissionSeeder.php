<?php

namespace Database\Seeders;

use App\Services\Rbac\RbacService;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Provisions the spatie roles/permissions from the matrix and mirrors the
     * existing tbl_users.classification values onto each user.
     */
    public function run(): void
    {
        $rbac = app(RbacService::class);

        $rbac->provisionMissingRoles();
        $counts = $rbac->provision();

        $this->command?->info("Roles: {$counts['roles']} | Permissions: {$counts['permissions']}");

        $result = $rbac->syncAllUsers();

        $this->command?->info("Users synced to roles: {$result['synced']}");

        if ($result['unmatched'] !== []) {
            $this->command?->warn('Unmatched classifications: '.implode(', ', $result['unmatched']));
        }
    }
}
