<?php

namespace App\Enums;

/**
 * Canonical application roles, mirrored one-to-one with the values stored in
 * tbl_users.classification and managed through spatie/laravel-permission.
 */
enum Role: string
{
    case SystemAdministrator = 'System Administrator';
    case DivisionAdministrator = 'Division Administrator';
    case DivisionSuperintendent = 'Division Superintendent';
    case AssistantDivisionSuperintendent = 'Assistant Division Superintendent';
    case ChiefOfCID = 'Chief of CID';
    case ChiefOfSGOD = 'Chief of SGOD';
    case DivisionSupervisor = 'Division Supervisor';
    case DistrictSupervisor = 'District Supervisor';
    case SchoolHead = 'School Head';
    case DepartmentHead = 'Department Head';
    case Teacher = 'Teacher';
    case Student = 'Student';

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromClassification(?string $classification): ?self
    {
        return self::tryFrom((string) $classification);
    }
}
