<?php

namespace App\Enums;

/**
 * The dimensions each user management tab can be filtered by.
 *
 * The value is the key the toolbar select posts (`filters[<value>]`), chosen by
 * the user rather than derived from a column, so a request can be matched
 * against this allow-list instead of reaching the query with a client supplied
 * name. Every case also carries the wording the toolbar renders, keeping the
 * labels next to the keys they belong to.
 */
enum UserManagementFilter: string
{
    /** The division, district or school the user belongs to. */
    case Area = 'area';

    /** A role: held by the user, or holding the permission. */
    case Role = 'roles';

    /** A user holding the role. */
    case User = 'user';

    /**
     * The filters available on the given tab.
     *
     * @return list<self>
     */
    public static function forTab(string $tab): array
    {
        return match ($tab) {
            'users' => [self::Area, self::Role],
            'roles' => [self::User],
            'permissions' => [self::Role],
            default => [],
        };
    }

    /**
     * The toolbar label of the filter.
     */
    public function label(): string
    {
        return match ($this) {
            self::Area => 'Area',
            self::Role => 'Role',
            self::User => 'User',
        };
    }

    /**
     * The wording of the "no filter applied" option.
     */
    public function placeholder(): string
    {
        return match ($this) {
            self::Area => 'All areas',
            self::Role => 'All roles',
            self::User => 'All users',
        };
    }
}
