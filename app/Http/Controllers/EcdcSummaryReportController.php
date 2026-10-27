<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateEcdcSummaryReport;
use App\Models\User;
use App\Services\EcdcSummaryReport;
use App\Support\UserHierarchy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EcdcSummaryReportController extends Controller
{
    public function store(Request $request, EcdcSummaryReport $reports): JsonResponse
    {
        $hierarchy = app(UserHierarchy::class)->forUser($request->user());
        if ($hierarchy['scope'] === 'school' && $request->input('scope', 'school') === 'school') {
            $request->merge(['scope' => 'school', 'scope_id' => $hierarchy['school_id']]);
        }
        $filters = $request->validate([
            'scope' => ['required', 'in:school,district,division'],
            'scope_id' => ['required', 'integer', 'min:1'],
            'academic_year_id' => ['required', 'integer', 'exists:tbl_academic_years,id'],
            'period' => ['required', 'integer', 'in:1,3'],
        ]);
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        foreach (['scope_id', 'academic_year_id', 'period'] as $field) {
            $filters[$field] = (int) $filters[$field];
        }
        ksort($filters);
        $reports->authorize($user, $filters);
        $key = 'ecdc-summary:request:'.$user->id.':'.hash('sha256', json_encode($filters));

        return Cache::lock($key.':lock', 10)->block(3, function () use ($key, $filters, $user): JsonResponse {
            $id = Cache::get($key);
            $state = $id ? Cache::get('ecdc-summary:'.$id) : null;
            if ($state && in_array($state['status'], ['queued', 'generating', 'ready'], true)
                && ($state['status'] !== 'ready' || (($state['completed_at'] ?? 0) > now()->subMinutes(10)->timestamp && Storage::disk('local')->exists($state['path'])))) {
                return response()->json($this->payload($id, $state));
            }

            $id = (string) Str::uuid();
            $state = ['user_id' => $user->id, 'filters' => $filters, 'status' => 'queued'];
            Cache::put('ecdc-summary:'.$id, $state, now()->addDay());
            Cache::put($key, $id, now()->addDay());
            try {
                GenerateEcdcSummaryReport::dispatch($id);
            } catch (\Throwable $exception) {
                Cache::forget($key);
                $state['status'] = 'failed';
                $state['message'] = 'Unable to queue this report.';
                Cache::put('ecdc-summary:'.$id, $state, now()->addDay());
                throw $exception;
            }

            return response()->json($this->payload($id, $state), 202);
        });
    }

    public function show(Request $request, string $id, EcdcSummaryReport $reports): JsonResponse
    {
        return response()->json($this->payload($id, $this->state($request, $id, $reports)));
    }

    public function download(Request $request, string $id, EcdcSummaryReport $reports): StreamedResponse
    {
        $state = $this->state($request, $id, $reports);
        abort_unless($state['status'] === 'ready', 409, 'The report is not ready.');
        abort_unless(Storage::disk('local')->exists($state['path']), 404);

        return Storage::disk('local')->download($state['path'], 'ECDC-'.$state['filters']['scope'].'-'.$state['filters']['period'].'.xlsx');
    }

    private function state(Request $request, string $id, EcdcSummaryReport $reports): array
    {
        $state = Cache::get('ecdc-summary:'.$id);
        abort_unless($state && (int) $state['user_id'] === (int) $request->user()->id, 404);
        $reports->authorize($request->user(), $state['filters']);

        return $state;
    }

    private function payload(string $id, array $state): array
    {
        return [
            'id' => $id,
            'status' => $state['status'],
            'message' => $state['message'] ?? null,
            'status_url' => route('reports.ecdc.show', $id),
            'download_url' => $state['status'] === 'ready' ? route('reports.ecdc.download', $id) : null,
        ];
    }
}
