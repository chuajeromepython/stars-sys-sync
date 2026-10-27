<?php

namespace App\Services;

use App\Models\District;
use App\Models\Division;
use App\Models\School;
use App\Models\User;
use App\Support\UserHierarchy;
use Illuminate\Database\Query\JoinClause;

final class RoleDashboardWelcomeContext
{
    public function __construct(private UserHierarchy $userHierarchy) {}

    /**
     * @return array{
     *     full_name: string,
     *     gender: string|null,
     *     level: string|null,
     *     school: string|null,
     *     school_code: string|null,
     *     district: string|null,
     *     division: string|null,
     *     system_wide: bool
     * }
     */
    public function forUser(User $user): array
    {
        $person = $user->person;
        $fullName = collect([
            $person?->first_name,
            $person?->middle_name,
            $person?->last_name,
            $person?->suffix,
        ])->filter()->implode(' ');
        $hierarchy = $this->userHierarchy->forUser($user);
        $context = [
            'full_name' => $fullName !== '' ? $fullName : $user->username,
            'gender' => $person?->gender === null
                ? null
                : ($person->gender === 'F' ? 'Female' : 'Male'),
            'level' => null,
            'school' => null,
            'school_code' => null,
            'district' => null,
            'division' => null,
            'system_wide' => $hierarchy['scope'] === 'system',
        ];

        if ($hierarchy['scope'] === 'system') {
            return $context;
        }

        if (in_array($hierarchy['scope'], ['school', 'teacher'], true) && $hierarchy['school_id'] !== null) {
            $school = School::query()
                ->join('tbl_districts', function (JoinClause $join): void {
                    $join->on('tbl_schools.district_id', '=', 'tbl_districts.id')
                        ->whereNull('tbl_districts.deleted_at');
                })
                ->join('tbl_divisions', function (JoinClause $join): void {
                    $join->on('tbl_districts.division_id', '=', 'tbl_divisions.id')
                        ->whereNull('tbl_divisions.deleted_at');
                })
                ->where('tbl_schools.id', $hierarchy['school_id'])
                ->select([
                    'tbl_schools.name as school',
                    'tbl_schools.code as school_code',
                    'tbl_districts.name as district',
                    'tbl_divisions.name as division',
                ])
                ->first();

            if ($school === null) {
                return $context;
            }

            return array_replace($context, [
                'level' => 'School',
                'school' => $school?->school,
                'school_code' => $school?->school_code,
                'district' => $school?->district,
                'division' => $school?->division,
            ]);
        }

        if ($hierarchy['district_id'] !== null) {
            $district = District::query()
                ->join('tbl_divisions', function (JoinClause $join): void {
                    $join->on('tbl_districts.division_id', '=', 'tbl_divisions.id')
                        ->whereNull('tbl_divisions.deleted_at');
                })
                ->where('tbl_districts.id', $hierarchy['district_id'])
                ->select('tbl_districts.name as district', 'tbl_divisions.name as division')
                ->first();

            return array_replace($context, [
                'level' => 'District',
                'district' => $district?->district,
                'division' => $district?->division,
            ]);
        }

        if ($hierarchy['division_id'] !== null) {
            $division = Division::query()->whereKey($hierarchy['division_id'])->value('name');

            if ($division === null) {
                return $context;
            }

            return array_replace($context, [
                'level' => 'Division',
                'division' => $division,
            ]);
        }

        return $context;
    }
}
