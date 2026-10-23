<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Support\ReportScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class ReportScopeTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    public function test_a_role_derives_the_reporting_level_it_sits_at(): void
    {
        $expectations = [
            Role::SystemAdministrator->value => ReportScope::Division,
            Role::DivisionAdministrator->value => ReportScope::Division,
            Role::DivisionSuperintendent->value => ReportScope::Division,
            Role::AssistantDivisionSuperintendent->value => ReportScope::Division,
            Role::ChiefOfCID->value => ReportScope::Division,
            Role::ChiefOfSGOD->value => ReportScope::Division,
            Role::DivisionSupervisor->value => ReportScope::Division,
            Role::DistrictSupervisor->value => ReportScope::District,
            Role::SchoolHead->value => ReportScope::School,
            Role::DepartmentHead->value => ReportScope::School,
            Role::Teacher->value => ReportScope::Teacher,
            Role::Student->value => ReportScope::None,
        ];

        foreach ($expectations as $role => $expected) {
            $scope = ReportScope::forUser($this->userWithRole(Role::from($role)));

            $this->assertSame(
                $expected,
                $scope,
                sprintf('[%s] should derive the [%s] reporting scope.', $role, $expected->name)
            );
        }
    }

    public function test_a_user_without_a_recognised_role_cannot_drill_down(): void
    {
        // This is the case that threw before: an unmapped classification left the
        // level undefined and the reports view failed while comparing against it.
        $scope = ReportScope::forUser($this->createUser(['classification' => 'Retired Personnel']));

        $this->assertSame(ReportScope::None, $scope);
        $this->assertFalse($scope->canDrillDownTo(ReportScope::Teacher));
        $this->assertFalse($scope->canDrillDownTo(ReportScope::School));
        $this->assertFalse($scope->canDrillDownTo(ReportScope::District));
    }

    public function test_a_guest_has_no_reporting_scope(): void
    {
        $this->assertSame(ReportScope::None, ReportScope::forUser(null));
    }

    public function test_a_user_holding_several_roles_drills_down_as_far_as_the_most_senior(): void
    {
        $user = $this->userWithRole(Role::Teacher);

        $this->assertSame(ReportScope::Teacher, ReportScope::forUser($user->fresh()));

        $user->fresh()->assignRole(Role::SchoolHead->value);

        $this->assertSame(ReportScope::School, ReportScope::forUser($user->fresh()));
    }

    /**
     * Pins the replacement to the numeric level the removed if-chain produced, so
     * the drill-down options offered on the reports page do not shift. The old
     * chain assigned Student no level at all, which is represented here by the
     * absence of every option.
     */
    public function test_the_drill_down_options_match_the_levels_the_view_previously_compared_against(): void
    {
        $previousLevels = [
            Role::SystemAdministrator->value => 5,
            Role::DivisionAdministrator->value => 4,
            Role::DivisionSuperintendent->value => 4,
            Role::AssistantDivisionSuperintendent->value => 4,
            Role::ChiefOfCID->value => 4,
            Role::ChiefOfSGOD->value => 4,
            Role::DivisionSupervisor->value => 4,
            Role::DistrictSupervisor->value => 3,
            Role::SchoolHead->value => 2,
            Role::DepartmentHead->value => 2,
            Role::Teacher->value => 1,
            Role::Student->value => 0,
        ];

        foreach ($previousLevels as $role => $level) {
            $scope = ReportScope::forUser($this->userWithRole(Role::from($role)));

            $this->assertSame(
                $level >= 4,
                $scope->canDrillDownTo(ReportScope::District),
                sprintf('[%s] district option.', $role)
            );

            $this->assertSame(
                $level >= 3,
                $scope->canDrillDownTo(ReportScope::School),
                sprintf('[%s] school option.', $role)
            );

            $this->assertSame(
                $level >= 2,
                $scope->canDrillDownTo(ReportScope::Teacher),
                sprintf('[%s] teacher option.', $role)
            );
        }
    }

    public function test_a_teacher_is_never_offered_a_filter_for_their_own_level(): void
    {
        $scope = ReportScope::forUser($this->userWithRole(Role::Teacher));

        // A teacher reports on their own class, so the scope ranks a user above
        // an entity before that entity is offered as a filter.
        $this->assertFalse($scope->canDrillDownTo(ReportScope::Teacher));
        $this->assertTrue(ReportScope::School->canDrillDownTo(ReportScope::Teacher));
        $this->assertFalse(ReportScope::School->canDrillDownTo(ReportScope::School));
    }
}
