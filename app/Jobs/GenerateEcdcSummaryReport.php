<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EcdcSummaryReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GenerateEcdcSummaryReport implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 600;

    public int $backoff = 10;

    public function __construct(public string $reportId)
    {
        $this->onConnection('ecdc_reports');
    }

    /**
     * Execute the job.
     */
    public function handle(EcdcSummaryReport $reports): void
    {
        $key = 'ecdc-summary:'.$this->reportId;
        $state = Cache::get($key);
        if (! $state || $state['status'] === 'ready') {
            return;
        }

        $user = User::findOrFail($state['user_id']);
        abort_unless($user->can('reports.generate'), 403);
        $reports->authorize($user, $state['filters']);
        $state['status'] = 'generating';
        Cache::put($key, $state, now()->addDay());
        $path = $reports->generate($this->reportId, $state['filters']);
        $state['status'] = 'ready';
        $state['path'] = $path;
        $state['completed_at'] = now()->timestamp;
        Cache::put($key, $state, now()->addDay());
    }

    public function failed(?Throwable $exception): void
    {
        $key = 'ecdc-summary:'.$this->reportId;
        $state = Cache::get($key);
        if ($state) {
            $state['status'] = 'failed';
            $state['message'] = 'The report could not be generated. Please try again.';
            Cache::put($key, $state, now()->addDay());
        }
    }
}
