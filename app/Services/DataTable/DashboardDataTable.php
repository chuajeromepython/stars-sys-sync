<?php

namespace App\Services\DataTable;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

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
    public function resolve(string $type): ?array
    {
        return match ($type) {
            'teachers' => $this->teachers(),
            'students' => $this->students(),
            'classrooms' => $this->classrooms(),
            default => null,
        };
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
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    private function teachers(): array
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
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    private function students(): array
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
     * @return array{query: Builder, columns: array<int, array<string, mixed>>}
     */
    private function classrooms(): array
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
