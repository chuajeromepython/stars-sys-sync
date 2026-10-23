<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('reports:prune-ecdc')]
#[Description('Remove generated ECDC report files older than two days')]
class PruneEcdcReports extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = Storage::disk('local');
        foreach ($disk->files('ecdc-reports') as $path) {
            if (preg_match('/^[0-9a-f-]{36}\.xlsx(?:\.tmp)?$/', basename($path))
                && $disk->lastModified($path) < now()->subDays(2)->timestamp) {
                $disk->delete($path);
            }
        }

        return self::SUCCESS;
    }
}
