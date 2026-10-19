<?php

namespace App\Services\DataTable;

use App\Enums\Role;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CustomFunction;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardDataTable
{
    /**
     * The record types the dashboard directory can display.
     */
    public const TYPES = ['teachers', 'students', 'classrooms'];

    /**
     * Resolve the base query and column definition for a dashboard record type.
     *
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}|null
     */
    public function resolve(string $type, ?Authenticatable $user = null): ?array
    {
        $user ??= Auth::user();

        if ($user === null) {
            return null;
        }

        $scope = $this->scope($user);

        return match ($type) {
            'teachers' => $this->teachers($scope),
            'students' => $this->students($scope),
            'classrooms' => $this->classrooms($scope),
            default => null,
        };
    }

    /**
     * The record types the dashboard directory may offer this user.
     *
     * A teacher manages classrooms and the students inside them but is not
     * responsible for the teacher roster, so the Teachers filter is hidden
     * rather than returning rows the user may not act on. A student belongs to
     * a classroom instead of administering one, so the whole directory is
     * hidden for that role.
     *
     * @return list<string>
     */
    public function typesFor(?Authenticatable $user = null): array
    {
        $user ??= Auth::user();

        if ($user === null) {
            return [];
        }

        return match (Role::fromClassification($user->classification ?? null)) {
            Role::Student => [],
            Role::Teacher => ['students', 'classrooms'],
            default => self::TYPES,
        };
    }

    /**
     * Whether the user is allowed to browse the given dashboard record type.
     */
    public function allows(?string $type, ?Authenticatable $user = null): bool
    {
        return $type !== null && in_array($type, $this->typesFor($user), true);
    }

    /**
     * Resolve the ids every dashboard query is scoped by.
     *
     * A teacher is scoped by their own teacher record, because their classroom
     * and student rows are reached through the classes they are assigned to.
     * Every other school scoped role is scoped by the school they belong to.
     * Division office roles resolve neither and keep the division wide view.
     *
     * @return array{teacher_id: int|null, school_id: int|null}
     */
    public function scope(?Authenticatable $user = null): array
    {
        $user ??= Auth::user();

        if ($user === null) {
            return ['teacher_id' => null, 'school_id' => null];
        }

        $teacherId = Teacher::where('user_id', $user->getAuthIdentifier())->value('id');

        if ($teacherId !== null) {
            return ['teacher_id' => (int) $teacherId, 'school_id' => CustomFunction::resolveSchoolIdForUser($user)];
        }

        return ['teacher_id' => null, 'school_id' => CustomFunction::resolveSchoolIdForUser($user)];
    }

    /**
     * The classroom ids a teacher scoped directory may show.
     *
     * @return list<int>|null Null means "no teacher scope", which callers read
     *                        as "do not narrow by classroom".
     */
    private function teacherClassroomIds(?int $teacherId): ?array
    {
        if ($teacherId === null) {
            return null;
        }

        return DB::table('tbl_teacher_classes')
            ->where('teacher_id', $teacherId)
            ->distinct()
            ->pluck('classroom_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Teachers joined to their user and person record, ordered by surname.
     *
     * The columns carry the only `searchable` flags the listing has: the action
     * column is markup, so it must never be searched. Everything else is left
     * searchable on purpose - the toolbar search box is then the same for every
     * record type, which is what a user expects when they switch the type
     * select. DataTables 3 removed `column().searchable()`, so the view can no
     * longer narrow the search at runtime; see the paginator, which honours a
     * server side flag before the one posted by the table.
     *
     * @param  array{teacher_id: int|null, school_id: int|null}  $scope
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    private function teachers(array $scope): array
    {
        $query = User::query()
            ->select([
                'tbl_teachers.id as id',
                'username',
                'tbl_persons.first_name',
                'tbl_persons.middle_name',
                'tbl_persons.last_name',
            ])
            ->join('tbl_teachers', 'tbl_teachers.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->where('classification', 'Teacher')
            ->orderBy('tbl_persons.last_name');

        // A school head and a department head see their own school roster; a
        // teacher has no roster responsibility and is not offered this filter.
        if ($scope['school_id'] !== null) {
            $query->where('tbl_teachers.school_id', $scope['school_id']);
        }

        return [
            'query' => $query,
            'columns' => [
                [
                    'data' => 'record',
                    'column' => 'tbl_persons.last_name',
                    'orderable' => true,
                    'render' => fn ($row, $value) => e(trim($row->first_name.' '.$row->last_name)),
                ],
                [
                    'data' => 'detail',
                    'source' => 'username',
                    'column' => 'username',
                    'orderable' => true,
                    'render' => fn ($row, $value) => e($value),
                ],
                [
                    'data' => 'action',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row) => '<a href="/teachers/'.(int) $row->id
                        .'/edit" class="btn btn-sm btn-primary"><i class="fas fa-pen"></i></a>',
                ],
            ],
        ];
    }

    /**
     * Students joined to their user and person record, ordered by surname.
     *
     * @param  array{teacher_id: int|null, school_id: int|null}  $scope
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    private function students(array $scope): array
    {
        $query = Student::query()
            ->select([
                'tbl_students.id as id',
                'lrn',
                'tbl_persons.first_name',
                'tbl_persons.middle_name',
                'tbl_persons.last_name',
            ])
            ->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
            ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
            ->orderBy('tbl_persons.last_name');

        $classroomIds = $this->teacherClassroomIds($scope['teacher_id']);

        // A teacher is responsible for the students inside the classrooms they
        // are assigned to, which is a narrower set than the whole school. EXISTS
        // rather than a join so a student enrolled in two of those classrooms is
        // still listed once.
        if ($classroomIds !== null) {
            $query->whereExists(function ($sub) use ($classroomIds) {
                $sub->select(DB::raw(1))
                    ->from('tbl_student_classrooms')
                    ->whereColumn('tbl_student_classrooms.student_id', 'tbl_students.id')
                    ->whereIn('tbl_student_classrooms.classroom_id', $classroomIds)
                    ->where('tbl_student_classrooms.status', 1);
            });
        } elseif ($scope['school_id'] !== null) {
            $query->where('tbl_students.school_id', $scope['school_id']);
        }

        return [
            'query' => $query,
            'columns' => [
                [
                    'data' => 'record',
                    'column' => 'tbl_persons.last_name',
                    'orderable' => true,
                    'render' => fn ($row, $value) => e(trim($row->last_name.', '.$row->first_name.' '.$row->middle_name)),
                ],
                [
                    'data' => 'detail',
                    'source' => 'lrn',
                    'column' => 'lrn',
                    'orderable' => true,
                    'render' => fn ($row, $value) => e($value),
                ],
                [
                    'data' => 'action',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row) => '<a href="/students/'.(int) $row->id
                        .'/edit" class="btn btn-sm btn-primary"><i class="fas fa-pen"></i></a>',
                ],
            ],
        ];
    }

    /**
     * Classrooms for the active academic year, ordered by grade level and section.
     *
     * @param  array{teacher_id: int|null, school_id: int|null}  $scope
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    private function classrooms(array $scope): array
    {
        $academicYear = AcademicYear::where('is_active', 1)->first();

        $query = Classroom::query()
            ->select([
                'tbl_classrooms.id as id',
                'tbl_sections.section as section',
                'tbl_grade_levels.level as level',
            ])
            ->join('tbl_sections', 'tbl_classrooms.section_id', 'tbl_sections.id')
            ->join('tbl_grade_levels', 'tbl_classrooms.grade_level_id', 'tbl_grade_levels.id')
            ->where('tbl_classrooms.academic_year_id', $academicYear?->id)
            ->orderBy('tbl_grade_levels.level')
            ->orderBy('tbl_sections.section');

        $classroomIds = $this->teacherClassroomIds($scope['teacher_id']);

        if ($classroomIds !== null) {
            $query->whereIn('tbl_classrooms.id', $classroomIds);
        } elseif ($scope['school_id'] !== null) {
            $query->where('tbl_classrooms.school_id', $scope['school_id']);
        }

        return [
            'query' => $query,
            'columns' => [
                [
                    'data' => 'record',
                    'column' => 'tbl_grade_levels.level',
                    'orderable' => true,
                    'render' => fn ($row, $value) => e($row->level.' - '.$row->section),
                ],
                [
                    'data' => 'detail',
                    'source' => 'level',
                    'column' => 'tbl_grade_levels.level',
                    'orderable' => true,
                    'render' => fn ($row, $value) => e($value),
                ],
                [
                    'data' => 'action',
                    'orderable' => false,
                    'searchable' => false,
                    'render' => fn ($row) => '<a href="/classrooms/'.(int) $row->id
                        .'" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>',
                ],
            ],
        ];
    }
}
