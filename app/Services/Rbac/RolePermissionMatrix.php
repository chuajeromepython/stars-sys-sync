<?php

namespace App\Services\Rbac;

use App\Enums\Role;

/**
 * Single source of truth for the permission catalogue and the role to
 * permission matrix. Permission names are "module.action", where the
 * supported actions are view, manage (create/update/delete) and upload.
 */
final class RolePermissionMatrix
{
    /**
     * Every permission known to the application, grouped per module.
     *
     * @var array<string, list<string>>
     */
    public const MODULES = [
        'academic_years' => ['view', 'manage'],
        'api' => ['lookups'],
        'assessments' => ['view', 'manage', 'upload'],
        'class_assessments' => ['view'],
        'classrooms' => ['view', 'manage', 'upload'],
        'competencies' => ['view', 'manage', 'upload'],
        'courses' => ['view', 'manage'],
        'dashboard' => ['view', 'manage'],
        'department_heads' => ['view', 'manage', 'upload'],
        'diagnostics' => ['view', 'upload'],
        'districts' => ['view', 'manage'],
        'divisions' => ['view', 'manage'],
        'ecdc_domains' => ['view', 'manage'],
        'ecdcs' => ['view', 'manage', 'upload', 'download'],
        'grade_levels' => ['view', 'manage'],
        'item_banks' => ['view'],
        'reports' => ['view', 'generate'],
        'roles' => ['view', 'manage', 'assign'],
        'permissions' => ['view', 'manage'],
        'schools' => ['view', 'manage'],
        'sections' => ['view', 'manage', 'upload'],
        'semesters' => ['view', 'manage'],
        'strands' => ['view', 'manage'],
        'students' => ['view', 'manage', 'upload'],
        'subject_components' => ['manage'],
        'subjects' => ['view', 'manage'],
        'summatives' => ['view', 'upload'],
        'teacher_classes' => ['view', 'manage', 'upload'],
        'teachers' => ['view', 'manage', 'upload'],
        'term_exams' => ['view', 'upload'],
        'tracks' => ['view', 'manage'],
        'trails' => ['view'],
        'users' => ['view', 'manage', 'reset', 'classification', 'upload'],
    ];

    /**
     * Read only grants shared by the division level office roles.
     *
     * @var list<string>
     */
    private const DIVISION_OFFICE = [
        'api.lookups',
        'classrooms.view',
        'competencies.view',
        'dashboard.view',
        'districts.view',
        'item_banks.view',
        'reports.view',
        'reports.generate',
        'schools.view',
        'sections.view',
        'students.view',
        'teachers.view',
    ];

    /**
     * Permissions granted to each role. A "module.*" entry expands to every
     * action declared for that module in self::MODULES.
     *
     * @var array<string, list<string>>
     */
    public const ROLE_PERMISSIONS = [
        Role::SystemAdministrator->value => ['*'],
        Role::DivisionAdministrator->value => [
            'academic_years.*',
            'api.lookups',
            'assessments.view',
            'class_assessments.view',
            'classrooms.view',
            'competencies.*',
            'courses.*',
            'dashboard.view',
            'department_heads.view',
            'districts.*',
            'ecdc_domains.*',
            'grade_levels.*',
            'item_banks.view',
            'permissions.*',
            'reports.view',
            'reports.generate',
            'roles.*',
            'schools.*',
            'sections.view',
            'semesters.*',
            'strands.*',
            'students.view',
            'subject_components.manage',
            'subjects.*',
            'teachers.view',
            'tracks.*',
            'users.*',
        ],
        Role::DivisionSuperintendent->value => self::DIVISION_OFFICE,
        Role::AssistantDivisionSuperintendent->value => self::DIVISION_OFFICE,
        Role::ChiefOfCID->value => self::DIVISION_OFFICE,
        Role::ChiefOfSGOD->value => self::DIVISION_OFFICE,
        Role::DivisionSupervisor->value => self::DIVISION_OFFICE,
        Role::DistrictSupervisor->value => self::DIVISION_OFFICE,
        Role::SchoolHead->value => [
            'api.lookups',
            'class_assessments.view',
            'classrooms.*',
            'competencies.view',
            'dashboard.view',
            'department_heads.*',
            'ecdcs.*',
            'item_banks.view',
            'reports.view',
            'reports.generate',
            'sections.*',
            'students.*',
            'teacher_classes.*',
            'teachers.*',
        ],
        Role::DepartmentHead->value => [
            'api.lookups',
            'assessments.*',
            'class_assessments.view',
            'classrooms.*',
            'competencies.view',
            'dashboard.view',
            'department_heads.view',
            'diagnostics.*',
            'ecdcs.*',
            'item_banks.view',
            'reports.view',
            'reports.generate',
            'sections.view',
            'students.*',
            'summatives.*',
            'teacher_classes.*',
            'teachers.*',
            'term_exams.*',
        ],
        Role::Teacher->value => [
            'api.lookups',
            'assessments.*',
            'class_assessments.view',
            'classrooms.view',
            'competencies.view',
            'dashboard.view',
            'diagnostics.*',
            'ecdcs.*',
            'item_banks.view',
            'reports.view',
            'reports.generate',
            'students.view',
            'summatives.*',
            'teacher_classes.*',
            'term_exams.*',
        ],
        Role::Student->value => [
            'class_assessments.view',
            'dashboard.view',
        ],
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::MODULES as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = $module.'.'.$action;
            }
        }

        sort($permissions);

        return array_values(array_unique($permissions));
    }

    /**
     * The permission names granted to the given role, expanding "module.*".
     *
     * @return list<string>
     */
    public static function forRole(Role $role): array
    {
        $grants = self::ROLE_PERMISSIONS[$role->value] ?? [];

        if (in_array('*', $grants, true)) {
            return self::all();
        }

        $permissions = [];

        foreach ($grants as $grant) {
            if (! str_ends_with($grant, '.*')) {
                $permissions[] = $grant;

                continue;
            }

            $module = substr($grant, 0, -2);

            foreach (self::MODULES[$module] ?? [] as $action) {
                $permissions[] = $module.'.'.$action;
            }
        }

        $permissions = array_values(array_unique($permissions));
        sort($permissions);

        return $permissions;
    }
}
