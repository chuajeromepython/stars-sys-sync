<?php

namespace App\Console\Commands;

use App\Services\Rbac\RbacService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('rbac:sync {--users : Also mirror tbl_users.classification onto each user role}')]
#[Description('Provision spatie roles and permissions from the role permission matrix')]
class SyncRolesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(RbacService $rbac): int
    {
        $rbac->provisionMissingRoles();
        $counts = $rbac->provision();

        $this->info("Provisioned {$counts['permissions']} permissions across {$counts['roles']} roles.");

        if ($this->option('users')) {
            $result = $rbac->syncAllUsers();

            $this->info("Synced {$result['synced']} user(s) to their classification role.");

            if ($result['unmatched'] !== []) {
                $this->warn('Unmatched classifications: '.implode(', ', $result['unmatched']));
            }
        }

        return self::SUCCESS;
    }
}
