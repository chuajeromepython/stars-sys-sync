<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\AcademicYear;
use App\Models\User;
use App\Support\RoleDataScope;
use App\Support\UserHierarchy;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class RoleDashboardData
{
    public function __construct(
        private RoleDataScope $roleDataScope,
        private UserHierarchy $userHierarchy,
    ) {}

    /**
     * @return array{
     *     role: string,
     *     heading: string,
     *     scope_label: string,
     *     summary: string,
     *     read_only: bool,
     *     metrics: list<array{label: string, value: int, icon: string}>,
     *     distribution_title: string,
     *     distribution: list<array{label: string, value: int, percentage: int}>
     * }
     */
    public function forUser(User $user, ?AcademicYear $academicYear): array
    {
        $role = Role::fromClassification($user->classification);
        $hierarchy = $this->userHierarchy->forUser($user);
        $schoolIds = $this->roleDataScope->schoolIdsFor($user);
        $classrooms = $this->classroomsFor($hierarchy, $schoolIds, $academicYear?->id);
        $metrics = [];
        $distribution = [];
        $distributionTitle = 'Data in your assigned scope';

        if ($role === Role::Student) {
            $metrics = $this->studentMetrics($user);
        } else {
            if ($user->can('schools.view')) {
                $metrics[] = $this->metric('Schools', count($schoolIds), 'fas fa-school');
            }

            if ($user->can('districts.view')) {
                $districtCount = $schoolIds === []
                    ? 0
                    : $this->roleDataScope->schoolsFor($user)->distinct()->count('district_id');
                $metrics[] = $this->metric('Districts', $districtCount, 'fas fa-map-marked-alt');
            }

            if ($user->can('classrooms.view')) {
                $metrics[] = $this->metric(
                    'Classrooms',
                    (int) (clone $classrooms)->distinct()->count('tbl_classrooms.id'),
                    'fas fa-chalkboard'
                );
            }

            if ($user->can('teachers.view')) {
                $teacherCount = (clone $classrooms)
                    ->join('tbl_teacher_classes', 'tbl_classrooms.id', '=', 'tbl_teacher_classes.classroom_id')
                    ->distinct()
                    ->count('tbl_teacher_classes.teacher_id');
                $metrics[] = $this->metric('Teachers', $teacherCount, 'fas fa-user-tie');
            }

            if ($user->can('students.view')) {
                $metrics[] = $this->metric(
                    'Students',
                    $this->studentCountForClassrooms($classrooms),
                    'fas fa-user-graduate'
                );
            }

            if ($user->can('sections.view')) {
                $sectionCount = (clone $classrooms)
                    ->distinct()
                    ->count('tbl_classrooms.section_id');
                $metrics[] = $this->metric('Sections', $sectionCount, 'fas fa-layer-group');
            }

            if ($hierarchy['scope'] === 'teacher' && $hierarchy['teacher_id'] !== null) {
                $metrics = array_merge(
                    $metrics,
                    $this->teacherMetrics($hierarchy['teacher_id'], $academicYear?->id)
                );
            }

            if (in_array($hierarchy['scope'], ['system', 'division', 'district'], true)
                && $user->can('schools.view')) {
                if ($user->can('districts.view')) {
                    $distributionTitle = $user->can('students.view')
                        ? 'Students by district'
                        : 'Schools by district';
                    $distribution = $this->districtDistribution(
                        $user,
                        $user->can('students.view')
                    );
                } else {
                    $distributionTitle = $user->can('students.view')
                        ? 'Students by school'
                        : 'Schools in your scope';
                    $distribution = $this->schoolDistribution(
                        $user,
                        $user->can('students.view')
                    );
                }
            } elseif (in_array($hierarchy['scope'], ['school', 'teacher'], true)
                && $user->can('classrooms.view')
                && $user->can('students.view')) {
                $distributionTitle = $hierarchy['scope'] === 'teacher'
                    ? 'Students by class responsibility'
                    : 'Students by grade level';
                $distribution = $hierarchy['scope'] === 'teacher'
                    && $hierarchy['teacher_id'] !== null
                        ? $this->teacherDistribution($hierarchy['teacher_id'], $academicYear?->id)
                        : $this->gradeDistribution($classrooms);
            }
        }

        return [
            'role' => $role?->value ?? 'Unassigned',
            'heading' => $this->headingFor($role),
            'scope_label' => $this->scopeLabel($hierarchy['scope']),
            'summary' => $this->summaryFor($role),
            'read_only' => in_array($role, [
                Role::DivisionSuperintendent,
                Role::AssistantDivisionSuperintendent,
            ], true),
            'metrics' => $metrics,
            'distribution_title' => $distributionTitle,
            'distribution' => $this->withPercentages($distribution),
        ];
    }

    /**
     * @param  array{scope: string, teacher_id: int|null, school_id: int|null, district_id: int|null, division_id: int|null}  $hierarchy
     * @param  list<int>  $schoolIds
     */
    private function classroomsFor(array $hierarchy, array $schoolIds, ?int $academicYearId): Builder
    {
        $classrooms = DB::table('tbl_classrooms')
            ->select('tbl_classrooms.id', 'tbl_classrooms.section_id')
            ->whereNull('tbl_classrooms.deleted_at');

        if ($schoolIds === []) {
            $classrooms->whereRaw('1 = 0');
        } else {
            $classrooms->whereIn('tbl_classrooms.school_id', $schoolIds);
        }

        if ($academicYearId !== null) {
            $classrooms->where('tbl_classrooms.academic_year_id', $academicYearId);
        }

        if ($hierarchy['scope'] === 'teacher') {
            if ($hierarchy['teacher_id'] === null) {
                $classrooms->whereRaw('1 = 0');
            } else {
                $classrooms->whereIn(
                    'tbl_classrooms.id',
                    DB::table('tbl_teacher_classes')
                        ->select('classroom_id')
                        ->where('teacher_id', $hierarchy['teacher_id'])
                );
            }
        }

        return $classrooms;
    }

    private function studentCountForClassrooms(Builder $classrooms): int
    {
        return DB::table('tbl_student_classrooms')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->whereIn('classroom_id', (clone $classrooms)->select('tbl_classrooms.id'))
            ->distinct()
            ->count('student_id');
    }

    /**
     * @return list<array{label: string, value: int, icon: string}>
     */
    private function teacherMetrics(int $teacherId, ?int $academicYearId): array
    {
        return [
            $this->metric(
                'Advisory Students',
                $this->teacherStudentCount($teacherId, $academicYearId, true),
                'fas fa-user-check'
            ),
            $this->metric(
                'Subject Class Students',
                $this->teacherStudentCount($teacherId, $academicYearId, false),
                'fas fa-book-open'
            ),
        ];
    }

    private function teacherStudentCount(int $teacherId, ?int $academicYearId, bool $advisory): int
    {
        $query = DB::table('tbl_student_classrooms')
            ->join('tbl_classrooms', 'tbl_student_classrooms.classroom_id', '=', 'tbl_classrooms.id')
            ->join('tbl_teacher_classes', 'tbl_classrooms.id', '=', 'tbl_teacher_classes.classroom_id')
            ->whereNull('tbl_student_classrooms.deleted_at')
            ->whereNull('tbl_classrooms.deleted_at')
            ->where('tbl_student_classrooms.status', 1)
            ->where('tbl_teacher_classes.teacher_id', $teacherId)
            ->where('tbl_teacher_classes.advisory', $advisory ? 1 : 0);

        if ($academicYearId !== null) {
            $query->where('tbl_classrooms.academic_year_id', $academicYearId);
        }

        return $query->distinct()->count('tbl_student_classrooms.student_id');
    }

    /**
     * @return list<array{label: string, value: int, icon: string}>
     */
    private function studentMetrics(User $user): array
    {
        $studentId = DB::table('tbl_students')
            ->where('user_id', $user->getKey())
            ->value('id');

        if ($studentId === null) {
            return [
                ['label' => 'My Assessments', 'value' => 0, 'icon' => 'fas fa-clipboard-check'],
                ['label' => 'My Scores', 'value' => 0, 'icon' => 'fas fa-chart-bar'],
            ];
        }

        $assessmentCount = DB::table('tbl_student_classrooms')
            ->join('tbl_teacher_classes', 'tbl_student_classrooms.classroom_id', '=', 'tbl_teacher_classes.classroom_id')
            ->join('tbl_class_assessments', 'tbl_teacher_classes.id', '=', 'tbl_class_assessments.class_id')
            ->where('tbl_student_classrooms.student_id', $studentId)
            ->where('tbl_student_classrooms.status', 1)
            ->whereNull('tbl_student_classrooms.deleted_at')
            ->distinct()
            ->count('tbl_class_assessments.id');

        $scoreCount = DB::table('tbl_student_scores')
            ->where('student_id', $studentId)
            ->whereNull('deleted_at')
            ->count();

        return [
            ['label' => 'My Assessments', 'value' => $assessmentCount, 'icon' => 'fas fa-clipboard-check'],
            ['label' => 'My Scores', 'value' => $scoreCount, 'icon' => 'fas fa-chart-bar'],
        ];
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function districtDistribution(User $user, bool $countStudents): array
    {
        $schoolScope = $this->roleDataScope->schoolsFor($user)
            ->select(['tbl_schools.id', 'tbl_schools.district_id']);

        $query = DB::query()
            ->fromSub($schoolScope, 'visible_schools')
            ->join('tbl_districts', 'visible_schools.district_id', '=', 'tbl_districts.id');

        if ($countStudents) {
            $query->leftJoin('tbl_students', 'visible_schools.id', '=', 'tbl_students.school_id');
        }

        $valueExpression = $countStudents
            ? 'COUNT(DISTINCT tbl_students.id)'
            : 'COUNT(DISTINCT visible_schools.id)';

        return $query
            ->groupBy('tbl_districts.id', 'tbl_districts.name')
            ->selectRaw('tbl_districts.name as label, '.$valueExpression.' as value')
            ->orderBy('tbl_districts.name')
            ->limit(12)
            ->get()
            ->map(static fn ($row): array => [
                'label' => (string) $row->label,
                'value' => (int) $row->value,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function schoolDistribution(User $user, bool $countStudents): array
    {
        $schools = $this->roleDataScope->schoolsFor($user)
            ->select(['tbl_schools.id', 'tbl_schools.name']);

        $query = DB::query()->fromSub($schools, 'visible_schools');

        if ($countStudents) {
            $query->leftJoin('tbl_students', 'visible_schools.id', '=', 'tbl_students.school_id');
        }

        $valueExpression = $countStudents
            ? 'COUNT(DISTINCT tbl_students.id)'
            : 'COUNT(DISTINCT visible_schools.id)';

        return $query
            ->groupBy('visible_schools.id', 'visible_schools.name')
            ->selectRaw('visible_schools.name as label, '.$valueExpression.' as value')
            ->orderBy('visible_schools.name')
            ->limit(12)
            ->get()
            ->map(static fn ($row): array => [
                'label' => (string) $row->label,
                'value' => (int) $row->value,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function gradeDistribution(Builder $classrooms): array
    {
        return DB::table('tbl_classrooms')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', '=', 'tbl_grade_levels.id')
            ->leftJoin('tbl_student_classrooms', function ($join): void {
                $join->on('tbl_classrooms.id', '=', 'tbl_student_classrooms.classroom_id')
                    ->where('tbl_student_classrooms.status', 1)
                    ->whereNull('tbl_student_classrooms.deleted_at');
            })
            ->whereIn('tbl_classrooms.id', (clone $classrooms)->select('tbl_classrooms.id'))
            ->groupBy('tbl_grade_levels.id', 'tbl_grade_levels.level')
            ->selectRaw('tbl_grade_levels.level as label, COUNT(DISTINCT tbl_student_classrooms.student_id) as value')
            ->orderBy('tbl_grade_levels.level')
            ->get()
            ->map(static fn ($row): array => [
                'label' => (string) $row->label,
                'value' => (int) $row->value,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function teacherDistribution(int $teacherId, ?int $academicYearId): array
    {
        $query = DB::table('tbl_teacher_classes')
            ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', '=', 'tbl_classrooms.id')
            ->leftJoin('tbl_student_classrooms', function ($join): void {
                $join->on('tbl_classrooms.id', '=', 'tbl_student_classrooms.classroom_id')
                    ->where('tbl_student_classrooms.status', 1)
                    ->whereNull('tbl_student_classrooms.deleted_at');
            })
            ->whereNull('tbl_classrooms.deleted_at')
            ->where('tbl_teacher_classes.teacher_id', $teacherId);

        if ($academicYearId !== null) {
            $query->where('tbl_classrooms.academic_year_id', $academicYearId);
        }

        return $query
            ->groupBy('tbl_teacher_classes.advisory')
            ->selectRaw(
                "CASE WHEN tbl_teacher_classes.advisory = 1 THEN 'Advisory' ELSE 'Subject Class' END as label, "
                .'COUNT(DISTINCT tbl_student_classrooms.student_id) as value'
            )
            ->orderBy('tbl_teacher_classes.advisory', 'desc')
            ->get()
            ->map(static fn ($row): array => [
                'label' => (string) $row->label,
                'value' => (int) $row->value,
            ])
            ->all();
    }

    /**
     * @param  list<array{label: string, value: int}>  $items
     * @return list<array{label: string, value: int, percentage: int}>
     */
    private function withPercentages(array $items): array
    {
        $maximum = max(array_column($items, 'value') ?: [0]);

        return array_map(
            static fn (array $item): array => $item + [
                'percentage' => $maximum > 0
                    ? (int) round(($item['value'] / $maximum) * 100)
                    : 0,
            ],
            $items
        );
    }

    /**
     * @return array{label: string, value: int, icon: string}
     */
    private function metric(string $label, int $value, string $icon): array
    {
        return ['label' => $label, 'value' => $value, 'icon' => $icon];
    }

    private function headingFor(?Role $role): string
    {
        return match ($role) {
            Role::DivisionSuperintendent => 'Division Performance Overview',
            Role::AssistantDivisionSuperintendent => 'Division Leadership Overview',
            Role::DivisionAdministrator => 'System Administration Overview',
            Role::DivisionSupervisor => 'District Operations Overview',
            Role::ChiefOfCID => 'Curriculum and Instruction Overview',
            Role::ChiefOfSGOD => 'School Governance and Operations Overview',
            Role::DistrictSupervisor => 'District School Overview',
            Role::SchoolHead => 'School Performance Overview',
            Role::DepartmentHead => 'Department Overview',
            Role::Teacher => 'Teaching and Class Overview',
            Role::Student => 'My Learning Overview',
            default => 'Dashboard',
        };
    }

    private function summaryFor(?Role $role): string
    {
        return match ($role) {
            Role::DivisionSuperintendent, Role::AssistantDivisionSuperintendent => 'A division-wide, read-only summary of the schools and learning activity within your assigned division.',
            Role::DivisionAdministrator => 'System setup and reference tools are available according to your assigned permissions.',
            Role::DivisionSupervisor => 'District and school indicators are limited to your assigned division.',
            Role::ChiefOfCID => 'Curriculum and instruction indicators are limited to the data your role is permitted to view.',
            Role::ChiefOfSGOD => 'School governance indicators are limited to the data your role is permitted to view.',
            Role::DistrictSupervisor => 'School indicators are limited to your assigned district.',
            Role::SchoolHead => 'School indicators are limited to your assigned school.',
            Role::DepartmentHead => 'School indicators are limited by your assignment and department permissions.',
            Role::Teacher => 'Class and student indicators include only your assigned advisory and subject classes.',
            Role::Student => 'This dashboard shows only your own assessment and score records.',
            default => 'Available information is restricted to your assigned access.',
        };
    }

    private function scopeLabel(string $scope): string
    {
        return match ($scope) {
            'system' => 'System-wide',
            'division' => 'Assigned division',
            'district' => 'Assigned district',
            'school' => 'Assigned school',
            'teacher' => 'Assigned classes',
            default => 'Personal records only',
        };
    }
}
