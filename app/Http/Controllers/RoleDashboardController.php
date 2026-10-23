<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\AcademicYear;
use App\Models\User;
use App\Services\DataTable\DataTablePaginator;
use App\Services\DataTable\RoleDashboardDirectory;
use App\Services\RoleDashboardData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RoleDashboardController extends Controller
{
    public function index(Request $request, RoleDashboardData $dashboardData): View
    {
        $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:tbl_academic_years,id'],
        ]);

        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $academicYears = AcademicYear::query()
            ->orderByDesc('from')
            ->orderByDesc('id')
            ->get();
        $academicYear = $request->filled('academic_year_id')
            ? AcademicYear::query()->findOrFail($request->integer('academic_year_id'))
            : AcademicYear::active();

        $page = [
            'name' => 'Dashboard',
            'title' => 'Dashboard',
            'crumb' => ['Dashboard' => '/dashboard'],
        ];
        $dashboard = $dashboardData->forUser($user, $academicYear);
        $person = $user->person;
        $role = Role::fromClassification($user->classification);
        $recordTypes = [];

        if ($role !== Role::Student) {
            if ($role !== Role::Teacher && $user->can('teachers.view')) {
                $recordTypes[] = 'teachers';
            }

            if ($user->can('students.view')) {
                $recordTypes[] = 'students';
            }

            if ($user->can('classrooms.view')) {
                $recordTypes[] = 'classrooms';
            }
        }

        $statistics = array_map(
            static fn (array $metric): array => $metric + ['url' => '#'],
            $dashboard['metrics']
        );
        $academic_year = $academicYear;

        return view('layouts.dashboard-role', compact(
            'page',
            'dashboard',
            'academicYear',
            'academicYears',
            'academic_year',
            'person',
            'recordTypes',
            'statistics'
        ));
    }

    public function records(
        Request $request,
        RoleDashboardDirectory $directory,
        DataTablePaginator $paginator,
    ): JsonResponse {
        $request->validate([
            'type' => ['nullable', 'string'],
            'academic_year_id' => ['nullable', 'integer', 'exists:tbl_academic_years,id'],
        ]);

        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $academicYearId = $request->filled('academic_year_id')
            ? $request->integer('academic_year_id')
            : AcademicYear::active()?->id;
        $type = $request->string('type')->toString();

        if (! $directory->allows($type, $user)) {
            return response()->json([
                'draw' => (int) $request->query('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $resolved = $directory->resolve(
            $type,
            $user,
            $academicYearId
        );

        return response()->json(
            $paginator->paginate($resolved['query'], $request, $resolved['columns'])
        );
    }
}
