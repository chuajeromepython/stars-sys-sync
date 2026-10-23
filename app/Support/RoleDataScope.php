<?php

namespace App\Support;

use App\Models\District;
use App\Models\Division;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class RoleDataScope
{
    public function __construct(private UserHierarchy $userHierarchy) {}

    /**
     * Resolve schools visible to the user's canonical organizational assignment.
     *
     * @return Builder<School>
     */
    public function schoolsFor(User $user): Builder
    {
        $hierarchy = $this->userHierarchy->forUser($user);
        $schools = School::query();

        return match ($hierarchy['scope']) {
            'system' => $schools,
            'division' => $schools->whereIn(
                'district_id',
                District::query()
                    ->select('id')
                    ->where('division_id', $hierarchy['division_id'])
            ),
            'district' => $hierarchy['district_id'] === null
                ? $schools->whereRaw('1 = 0')
                : $schools->whereIn(
                    'district_id',
                    District::query()->select('id')->whereKey($hierarchy['district_id'])
                ),
            'school', 'teacher' => $hierarchy['school_id'] === null
                ? $schools->whereRaw('1 = 0')
                : $schools
                    ->whereKey($hierarchy['school_id'])
                    ->whereIn('district_id', District::query()->select('id')),
            default => $schools->whereRaw('1 = 0'),
        };
    }

    /**
     * @return list<int>
     */
    public function schoolIdsFor(User $user): array
    {
        return $this->schoolsFor($user)
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    public function allowsSchool(User $user, int $schoolId): bool
    {
        return $this->schoolsFor($user)->whereKey($schoolId)->exists();
    }

    /**
     * Resolve districts visible to the user's canonical organizational assignment.
     *
     * @return Builder<District>
     */
    public function districtsFor(User $user): Builder
    {
        $hierarchy = $this->userHierarchy->forUser($user);
        $districts = District::query();

        return match ($hierarchy['scope']) {
            'system' => $districts,
            'division' => $districts->where('division_id', $hierarchy['division_id']),
            'district' => $districts->whereKey($hierarchy['district_id']),
            'school', 'teacher' => $hierarchy['district_id'] === null
                ? $districts->whereRaw('1 = 0')
                : $districts->whereKey($hierarchy['district_id']),
            default => $districts->whereRaw('1 = 0'),
        };
    }

    /**
     * Resolve divisions visible to the user's canonical organizational assignment.
     *
     * @return Builder<Division>
     */
    public function divisionsFor(User $user): Builder
    {
        $hierarchy = $this->userHierarchy->forUser($user);
        $divisions = Division::query();

        return match ($hierarchy['scope']) {
            'system' => $divisions,
            'division' => $divisions->whereKey($hierarchy['division_id']),
            'district', 'school', 'teacher' => $hierarchy['division_id'] === null
                ? $divisions->whereRaw('1 = 0')
                : $divisions->whereKey($hierarchy['division_id']),
            default => $divisions->whereRaw('1 = 0'),
        };
    }
}
