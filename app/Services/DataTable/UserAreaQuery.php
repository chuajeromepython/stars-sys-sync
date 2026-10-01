<?php

namespace App\Services\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the "area" a user belongs to.
 *
 * Area is not stored on tbl_users: it is implied by whichever profile table
 * links the user to a division, a district or a school. A single user has at
 * most one such profile, so the areas are unioned into one derived table and
 * reduced to a single row per user. The legacy listing joined one table at a
 * time based on the selected classification, which cannot work for a listing
 * that spans every classification at once.
 */
final class UserAreaQuery
{
    /**
     * Profile tables pointing at a division, in the order they are unioned.
     *
     * @var list<array{0: string, 1: string}> [profile table, division foreign key]
     */
    private const DIVISION_PROFILES = [
        ['tbl_division_administrators', 'division_id'],
        ['tbl_division_supervisors', 'division_id'],
        ['tbl_division_superintendents', 'division_id'],
        ['tbl_asst_division_superintendents', 'division_id'],
        ['tbl_chief_cids', 'division_id'],
        ['tbl_chief_sgods', 'division_id'],
    ];

    /**
     * Profile tables pointing at a district.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const DISTRICT_PROFILES = [
        ['tbl_district_supervisors', 'district_id'],
    ];

    /**
     * Profile tables pointing at a school.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const SCHOOL_PROFILES = [
        ['tbl_school_supervisors', 'school_id'],
        ['tbl_department_heads', 'school_id'],
        ['tbl_teachers', 'school_id'],
        ['tbl_students', 'school_id'],
    ];

    /**
     * Left join the resolved area onto a user query, aliased as `area`.
     */
    public function join(Builder $query, string $alias = 'area'): Builder
    {
        return $query
            ->leftJoinSub($this->subQuery(), $alias, $alias.'.user_id', 'tbl_users.id')
            ->addSelect($alias.'.name as area');
    }

    /**
     * Every area name a user may be filtered by, alphabetically ordered.
     *
     * The option list has no interest in which user an area belongs to, so it
     * is read straight from the union. Reducing it first would require grouping
     * by user_id, and selecting a bare `name` alongside that grouping is
     * rejected by MySQL's ONLY_FULL_GROUP_BY mode.
     *
     * @return list<array{label: string, value: string}>
     */
    public function options(): array
    {
        return DB::query()
            ->fromSub($this->areas(), 'user_areas')
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->map(fn (string $name): array => ['label' => $name, 'value' => $name])
            ->values()
            ->all();
    }

    /**
     * The union of every profile area, reduced to one row per user.
     */
    private function subQuery(): QueryBuilder
    {
        return DB::query()
            ->fromSub($this->areas(), 'user_areas')
            ->select(['user_id'])
            ->selectRaw('MIN(name) as name')
            ->groupBy('user_id');
    }

    /**
     * Every profile area as a single derived table, one row per profile.
     */
    private function areas(): QueryBuilder
    {
        return $this->union(self::DIVISION_PROFILES, 'tbl_divisions')
            ->unionAll($this->union(self::DISTRICT_PROFILES, 'tbl_districts'))
            ->unionAll($this->union(self::SCHOOL_PROFILES, 'tbl_schools'));
    }

    /**
     * One branch of the union: every profile row joined to its area table.
     *
     * @param  list<array{0: string, 1: string}>  $profiles
     */
    private function union(array $profiles, string $areas): QueryBuilder
    {
        $union = null;

        foreach ($profiles as [$profile, $foreignKey]) {
            // A single select() call, because a second select() replaces the
            // first rather than appending to it.
            $branch = DB::query()
                ->from($profile)
                ->join($areas, $areas.'.id', $profile.'.'.$foreignKey)
                ->select([$profile.'.user_id as user_id', $areas.'.name as name'])
                ->whereNull($profile.'.deleted_at');

            $union = $union === null ? $branch : $union->unionAll($branch);
        }

        return $union;
    }
}
