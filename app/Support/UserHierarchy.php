<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\AssistantDivisionSuperIntendent;
use App\Models\ChiefCID;
use App\Models\ChiefSGOD;
use App\Models\DepartmentHead;
use App\Models\District;
use App\Models\DistrictSupervisor;
use App\Models\DivisionAdministrator;
use App\Models\DivisionSuperIntendent;
use App\Models\DivisionSupervisor;
use App\Models\School;
use App\Models\SchoolSupervisor;
use App\Models\Teacher;
use App\Models\User;

class UserHierarchy
{
    /**
     * Resolve the organisational assignment carried by a user's account.
     *
     * A recognised role with no matching assignment returns the `none` scope.
     * Callers must treat that as no records rather than accidentally widening
     * the query to the whole installation.
     *
     * @return array{scope: 'system'|'division'|'district'|'school'|'teacher'|'none', teacher_id: int|null, school_id: int|null, district_id: int|null, division_id: int|null}
     */
    public function forUser(User $user): array
    {
        $role = Role::fromClassification($user->classification);
        $teacherId = null;
        $schoolId = null;
        $districtId = null;
        $divisionId = null;

        if ($role === Role::SystemAdministrator) {
            return $this->result('system');
        }

        if ($role === Role::Teacher) {
            $teacher = Teacher::query()->where('user_id', $user->id)->first(['id', 'school_id']);
            $teacherId = $teacher?->id;
            $schoolId = $teacher?->school_id;
        } elseif (in_array($role, [Role::SchoolHead, Role::DepartmentHead], true)) {
            $schoolId = match ($role) {
                Role::SchoolHead => SchoolSupervisor::query()->where('user_id', $user->id)->value('school_id'),
                Role::DepartmentHead => DepartmentHead::query()->where('user_id', $user->id)->value('school_id'),
            };
        } elseif ($role === Role::DistrictSupervisor) {
            $districtId = DistrictSupervisor::query()->where('user_id', $user->id)->value('district_id');
        } elseif (in_array($role, [
            Role::DivisionAdministrator,
            Role::DivisionSupervisor,
            Role::DivisionSuperintendent,
            Role::AssistantDivisionSuperintendent,
            Role::ChiefOfCID,
            Role::ChiefOfSGOD,
        ], true)) {
            $divisionId = match ($role) {
                Role::DivisionAdministrator => DivisionAdministrator::query()->where('user_id', $user->id)->value('division_id'),
                Role::DivisionSupervisor => DivisionSupervisor::query()->where('user_id', $user->id)->value('division_id'),
                Role::DivisionSuperintendent => DivisionSuperIntendent::query()->where('user_id', $user->id)->value('division_id'),
                Role::AssistantDivisionSuperintendent => AssistantDivisionSuperIntendent::query()->where('user_id', $user->id)->value('division_id'),
                Role::ChiefOfCID => ChiefCID::query()->where('user_id', $user->id)->value('division_id'),
                Role::ChiefOfSGOD => ChiefSGOD::query()->where('user_id', $user->id)->value('division_id'),
            };
        }

        if ($schoolId !== null) {
            $districtId = School::query()->whereKey($schoolId)->value('district_id');
        }

        if ($districtId !== null) {
            $divisionId = District::query()->whereKey($districtId)->value('division_id');
        }

        $scope = match (true) {
            $teacherId !== null => 'teacher',
            $schoolId !== null => 'school',
            $districtId !== null => 'district',
            $divisionId !== null => 'division',
            default => 'none',
        };

        return $this->result($scope, $teacherId, $schoolId, $districtId, $divisionId);
    }

    /**
     * @return array{scope: 'system'|'division'|'district'|'school'|'teacher'|'none', teacher_id: int|null, school_id: int|null, district_id: int|null, division_id: int|null}
     */
    private function result(
        string $scope,
        ?int $teacherId = null,
        ?int $schoolId = null,
        ?int $districtId = null,
        ?int $divisionId = null,
    ): array {
        return [
            'scope' => $scope,
            'teacher_id' => $teacherId,
            'school_id' => $schoolId,
            'district_id' => $districtId,
            'division_id' => $divisionId,
        ];
    }
}
