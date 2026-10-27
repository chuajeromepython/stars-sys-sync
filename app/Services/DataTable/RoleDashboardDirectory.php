<?php

namespace App\Services\DataTable;

use App\Enums\Role;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Support\RoleDataScope;
use App\Support\UserHierarchy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class RoleDashboardDirectory
{
    public function __construct(
        private RoleDataScope $roleDataScope,
        private UserHierarchy $userHierarchy,
    ) {}

    /**
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    public function resolve(string $type, User $user, ?int $academicYearId): array
    {
        $permission = match ($type) {
            'teachers' => 'teachers.view',
            'students' => 'students.view',
            'classrooms' => 'classrooms.view',
            default => abort(404),
        };

        $selfOnlyStudent = Role::fromClassification($user->classification) === Role::Student
            && $type === 'students';

        abort_unless($selfOnlyStudent || $user->can($permission), 403);

        return match ($type) {
            'teachers' => $this->teachers($user, $academicYearId),
            'students' => $this->students($user, $academicYearId, $selfOnlyStudent),
            'classrooms' => $this->classrooms($user, $academicYearId),
        };
    }

    public function allows(string $type, User $user): bool
    {
        if (! in_array($type, ['teachers', 'students', 'classrooms'], true)) {
            return false;
        }

        if ($type === 'students' && Role::fromClassification($user->classification) === Role::Student) {
            return true;
        }

        $permission = $type.'.view';

        return $user->can($permission);
    }

    /**
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    private function teachers(User $user, ?int $academicYearId): array
    {
        $schoolIds = $this->roleDataScope->schoolIdsFor($user);
        $query = User::query()
            ->select([
                'tbl_teachers.id as id',
                'tbl_users.username',
                'tbl_persons.first_name',
                'tbl_persons.middle_name',
                'tbl_persons.last_name',
            ])
            ->join('tbl_teachers', 'tbl_teachers.user_id', '=', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', '=', 'tbl_persons.id')
            ->where('tbl_users.classification', Role::Teacher->value)
            ->whereIn('tbl_teachers.school_id', $schoolIds)
            ->orderBy('tbl_persons.last_name')
            ->orderBy('tbl_persons.first_name');

        if ($academicYearId !== null) {
            $query->whereExists(function ($subquery) use ($academicYearId): void {
                $subquery->selectRaw('1')
                    ->from('tbl_teacher_classes')
                    ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', '=', 'tbl_classrooms.id')
                    ->whereColumn('tbl_teacher_classes.teacher_id', 'tbl_teachers.id')
                    ->where('tbl_classrooms.academic_year_id', $academicYearId)
                    ->whereNull('tbl_classrooms.deleted_at');
            });
        }

        $columns = [
            [
                'data' => 'record',
                'column' => 'tbl_persons.last_name',
                'orderable' => true,
                'render' => static fn ($row): string => e(trim($row->last_name.', '.$row->first_name.' '.$row->middle_name)),
            ],
            [
                'data' => 'detail',
                'source' => 'username',
                'column' => 'tbl_users.username',
                'orderable' => true,
            ],
        ];

        $columns[] = [
            'data' => 'action',
            'orderable' => false,
            'searchable' => false,
            'render' => $user->can('teachers.manage')
                ? static fn ($row): string => '<a href="/teachers/'.(int) $row->id
                    .'/edit" class="btn btn-sm btn-primary"><i class="fas fa-pen"></i><span class="sr-only">Edit</span></a>'
                : static fn (): string => '',
        ];

        return ['query' => $query, 'columns' => $columns];
    }

    /**
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    private function students(User $user, ?int $academicYearId, bool $selfOnly): array
    {
        $query = Student::query()
            ->select([
                'tbl_students.id as id',
                'tbl_students.lrn',
                'tbl_persons.first_name',
                'tbl_persons.middle_name',
                'tbl_persons.last_name',
            ])
            ->join('tbl_users', 'tbl_students.user_id', '=', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', '=', 'tbl_persons.id')
            ->orderBy('tbl_persons.last_name')
            ->orderBy('tbl_persons.first_name');

        if ($selfOnly) {
            $query->where('tbl_students.user_id', $user->getKey());
        } else {
            $query->whereIn('tbl_students.school_id', $this->roleDataScope->schoolIdsFor($user));

            $hierarchy = $this->userHierarchy->forUser($user);

            if ($hierarchy['scope'] === 'teacher') {
                if ($hierarchy['teacher_id'] === null) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereExists(function ($subquery) use ($hierarchy): void {
                        $subquery->selectRaw('1')
                            ->from('tbl_student_classrooms')
                            ->join('tbl_teacher_classes', 'tbl_student_classrooms.classroom_id', '=', 'tbl_teacher_classes.classroom_id')
                            ->join('tbl_classrooms', 'tbl_teacher_classes.classroom_id', '=', 'tbl_classrooms.id')
                            ->whereColumn('tbl_student_classrooms.student_id', 'tbl_students.id')
                            ->where('tbl_student_classrooms.status', 1)
                            ->whereNull('tbl_student_classrooms.deleted_at')
                            ->whereNull('tbl_classrooms.deleted_at')
                            ->where('tbl_teacher_classes.teacher_id', $hierarchy['teacher_id']);
                    });
                }
            }
        }

        if ($academicYearId !== null) {
            $query->whereExists(function ($subquery) use ($academicYearId): void {
                $subquery->selectRaw('1')
                    ->from('tbl_student_classrooms')
                    ->join('tbl_classrooms', 'tbl_student_classrooms.classroom_id', '=', 'tbl_classrooms.id')
                    ->whereColumn('tbl_student_classrooms.student_id', 'tbl_students.id')
                    ->where('tbl_student_classrooms.status', 1)
                    ->where('tbl_classrooms.academic_year_id', $academicYearId)
                    ->whereNull('tbl_student_classrooms.deleted_at')
                    ->whereNull('tbl_classrooms.deleted_at');
            });
        }

        $columns = [
            [
                'data' => 'record',
                'column' => 'tbl_persons.last_name',
                'orderable' => true,
                'render' => static fn ($row): string => e(trim($row->last_name.', '.$row->first_name.' '.$row->middle_name)),
            ],
            [
                'data' => 'detail',
                'source' => 'lrn',
                'column' => 'tbl_students.lrn',
                'orderable' => true,
            ],
        ];

        $columns[] = [
            'data' => 'action',
            'orderable' => false,
            'searchable' => false,
            'render' => $user->can('students.manage')
                ? static fn ($row): string => '<a href="/students/'.(int) $row->id
                    .'/edit" class="btn btn-sm btn-primary"><i class="fas fa-pen"></i><span class="sr-only">Edit</span></a>'
                : static fn (): string => '',
        ];

        return ['query' => $query, 'columns' => $columns];
    }

    /**
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    private function classrooms(User $user, ?int $academicYearId): array
    {
        $hierarchy = $this->userHierarchy->forUser($user);
        $query = Classroom::query()
            ->select([
                'tbl_classrooms.id as id',
                'tbl_grade_levels.level',
                'tbl_sections.section',
            ])
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', '=', 'tbl_grade_levels.id')
            ->join('tbl_sections', 'tbl_classrooms.section_id', '=', 'tbl_sections.id')
            ->whereIn('tbl_classrooms.school_id', $this->roleDataScope->schoolIdsFor($user))
            ->orderBy('tbl_grade_levels.level')
            ->orderBy('tbl_sections.section');

        if ($academicYearId !== null) {
            $query->where('tbl_classrooms.academic_year_id', $academicYearId);
        } else {
            $activeYearId = AcademicYear::active()?->id;

            if ($activeYearId !== null) {
                $query->where('tbl_classrooms.academic_year_id', $activeYearId);
            }
        }

        if ($hierarchy['scope'] === 'teacher') {
            if ($hierarchy['teacher_id'] === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn(
                    'tbl_classrooms.id',
                    DB::table('tbl_teacher_classes')
                        ->select('classroom_id')
                        ->where('teacher_id', $hierarchy['teacher_id'])
                );
            }
        }

        return [
            'query' => $query,
            'columns' => [
                [
                    'data' => 'record',
                    'column' => 'tbl_grade_levels.level',
                    'orderable' => true,
                    'render' => static fn ($row): string => e($row->level.' - '.$row->section),
                ],
                [
                    'data' => 'detail',
                    'source' => 'level',
                    'column' => 'tbl_grade_levels.level',
                    'orderable' => true,
                ],
                [
                    'data' => 'action',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => $user->can('classrooms.manage')
                        ? static fn ($row): string => '<a href="/classrooms/'.(int) $row->id
                            .'" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i><span class="sr-only">View</span></a>'
                        : static fn (): string => '',
                ],
            ],
        ];
    }
}
