<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * How far a user may drill down when generating reports.
 *
 * The level used to be derived from the legacy tbl_users.classification string
 * through an if-chain in ReportController::index(), which left the level
 * undefined for any classification that was not explicitly listed and made the
 * reports screen throw on the way to the view. Deriving it from the spatie roles
 * the user actually holds keeps this in step with the permissions that gate the
 * routes and never leaves the caller without a value.
 *
 * A scope is the organisational level the user sits at, so drilling down means
 * reaching an entity ranked below it: a School Head narrows reports to a
 * teacher, a District Supervisor to a school, and division level staff to a
 * district.
 */
enum ReportScope: int
{
    /** Held when the user has no role that carries a reporting level. */
    case None = 0;

    case Teacher = 1;

    case School = 2;

    case District = 3;

    case Division = 4;

    /**
     * The scope carried by each application role.
     *
     * System Administrator sits at division level, matching the level five the
     * previous if-chain assigned it and satisfying every drill-down the view
     * offers.
     *
     * @var array<string, self>
     */
    public static function roleScopes(): array
    {
        return [
            Role::SystemAdministrator->value => self::Division,
            Role::DivisionAdministrator->value => self::Division,
            Role::DivisionSuperintendent->value => self::Division,
            Role::AssistantDivisionSuperintendent->value => self::Division,
            Role::ChiefOfCID->value => self::Division,
            Role::ChiefOfSGOD->value => self::Division,
            Role::DivisionSupervisor->value => self::Division,
            Role::DistrictSupervisor->value => self::District,
            Role::SchoolHead->value => self::School,
            Role::DepartmentHead->value => self::School,
            Role::Teacher->value => self::Teacher,
            Role::Student->value => self::None,
        ];
    }

    /**
     * The widest scope across every role the given user holds.
     *
     * A user granted several roles through the role management screens is
     * allowed to drill down as far as the most senior of them, so a Teacher who
     * also holds School Head reaches schools. Returns None for an unauthenticated
     * user, one with no recognised role, or a role outside the matrix.
     */
    public static function forUser(?Authenticatable $user): self
    {
        if (! $user instanceof User) {
            return self::None;
        }

        $scopes = self::roleScopes();
        $scope = self::None;

        foreach ($user->getRoleNames() as $name) {
            $held = $scopes[(string) $name] ?? null;

            if ($held !== null && $held->value > $scope->value) {
                $scope = $held;
            }
        }

        return $scope;
    }

    /**
     * The numeric level, for comparisons and for tests asserting ordering.
     */
    public function level(): int
    {
        return $this->value;
    }

    /**
     * Whether this scope may narrow reports down to the given entity.
     *
     * Strictly greater rather than greater-or-equal: a teacher reports on their
     * own class and is not offered a teacher filter, the same behaviour the
     * numeric level encoded by starting the options one rung above the user.
     */
    public function canDrillDownTo(self $target): bool
    {
        return $this->value > $target->value;
    }
}
