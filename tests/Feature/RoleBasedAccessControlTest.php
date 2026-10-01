<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use App\Services\Rbac\RbacService;
use App\Services\Rbac\RolePermissionMatrix;
use App\Support\AssessmentTab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RouteDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    use InteractsWithRbac, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provisionRbac();
    }

    public function test_it_creates_every_role_and_permission_from_the_matrix(): void
    {
        $this->assertSame(count(Role::cases()), SpatieRole::count());
        $this->assertSame(count(RolePermissionMatrix::all()), Permission::count());

        foreach (RolePermissionMatrix::all() as $name) {
            $this->assertNotNull(Permission::findByName($name));
        }
    }

    public function test_the_system_administrator_receives_every_permission(): void
    {
        $admin = SpatieRole::findByName(Role::SystemAdministrator->value);

        $this->assertCount(count(RolePermissionMatrix::all()), $admin->permissions);
    }

    public function test_a_role_only_receives_the_permissions_declared_for_it(): void
    {
        $teacher = SpatieRole::findByName(Role::Teacher->value);

        $this->assertTrue($teacher->hasPermissionTo('assessments.upload'));
        $this->assertFalse($teacher->hasPermissionTo('users.view'));
        $this->assertFalse($teacher->hasPermissionTo('divisions.manage'));
    }

    public function test_saving_a_user_mirrors_the_classification_onto_a_role(): void
    {
        $user = $this->userWithRole(Role::SchoolHead);

        $this->assertTrue($user->hasRole(Role::SchoolHead->value));
        $this->assertFalse($user->hasRole(Role::Teacher->value));
    }

    public function test_changing_the_classification_adds_the_matching_role(): void
    {
        $user = $this->userWithRole(Role::Teacher);

        $user->classification = Role::DivisionAdministrator->value;
        $user->save();

        // The classification role is granted additively so a user can hold
        // several roles at once; the previous role is not revoked here.
        $this->assertTrue($user->fresh()->hasRole(Role::DivisionAdministrator->value));
        $this->assertTrue($user->fresh()->hasRole(Role::Teacher->value));
    }

    public function test_an_unknown_classification_is_left_without_a_role(): void
    {
        $user = $this->userWithRole(Role::Teacher);

        $user->classification = 'Retired Personnel';
        $user->save();

        $this->assertCount(0, $user->fresh()->roles);
    }

    public function test_sync_all_users_reports_unmatched_classifications(): void
    {
        $this->userWithRole(Role::Teacher);
        $this->userWithRole(Role::Teacher, ['username' => 'legacy@deped.gov.ph']);
        User::where('username', 'legacy@deped.gov.ph')->update(['classification' => 'Legacy Officer']);

        $result = app(RbacService::class)->syncAllUsers();

        $this->assertSame(1, $result['synced']);
        $this->assertSame(['Legacy Officer'], $result['unmatched']);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/teachers')->assertRedirect('/login');
    }

    public function test_an_authenticated_user_without_the_permission_is_redirected_to_forbidden(): void
    {
        $teacher = $this->userWithRole(Role::Teacher);

        $this->actingAs($teacher)->get('/users')->assertRedirect('/forbidden');
    }

    public function test_an_authenticated_user_with_the_permission_passes_the_middleware(): void
    {
        $schoolHead = $this->userWithRole(Role::SchoolHead);

        $response = $this->actingAs($schoolHead)->get('/users');

        $this->assertNotSame('/forbidden', $response->headers->get('Location'));
        $this->assertNotSame(403, $response->getStatusCode());
    }

    public function test_a_json_request_without_the_permission_receives_a_403(): void
    {
        $teacher = $this->userWithRole(Role::Teacher);

        $this->actingAs($teacher)->getJson('/teachers/data')->assertForbidden();
    }

    public function test_the_sidebar_only_renders_links_the_role_may_access(): void
    {
        $teacher = $this->actingAs($this->userWithRole(Role::Teacher));

        $html = $teacher->view('layouts.sidebar', [
            'page' => ['name' => 'Dashboard'],
            'classification' => Role::Teacher->value,
        ])->__toString();

        $this->assertStringContainsString('href="/item_banks"', $html);
        $this->assertStringContainsString('href="/term-exams"', $html);
        $this->assertStringNotContainsString('href="/users"', $html);
        $this->assertStringNotContainsString('href="/divisions"', $html);
        $this->assertStringNotContainsString('href="/trails"', $html);

        $schoolHead = $this->actingAs($this->userWithRole(Role::SchoolHead));

        $html = $schoolHead->view('layouts.sidebar', [
            'page' => ['name' => 'Dashboard'],
            'classification' => Role::SchoolHead->value,
        ])->__toString();

        $this->assertStringContainsString('href="/department_heads"', $html);
        $this->assertStringNotContainsString('href="/term-exams"', $html);
        $this->assertStringNotContainsString('href="/users"', $html);
    }

    public function test_the_sidebar_lists_each_destination_once(): void
    {
        $html = $this->actingAs($this->userWithRole(Role::SchoolHead))
            ->view('layouts.sidebar', [
                'page' => ['name' => 'Dashboard'],
                'classification' => Role::SchoolHead->value,
            ])->__toString();

        foreach (['/competencies', '/teachers', '/students', '/sections', '/classrooms'] as $url) {
            $this->assertSame(1, substr_count($html, 'href="'.$url.'"'), "[$url] should appear exactly once.");
        }
    }

    public function test_the_assessment_entry_points_at_the_first_permitted_tab(): void
    {
        $teacher = $this->actingAs($this->userWithRole(Role::Teacher));

        $html = $teacher->view('layouts.sidebar', [
            'page' => ['name' => 'Dashboard'],
            'classification' => Role::Teacher->value,
        ])->__toString();

        $this->assertStringContainsString('href="/term-exams"', $html);
        $this->assertStringNotContainsString('href="/diagnostics"', $html);
        $this->assertStringNotContainsString('href="/summatives"', $html);
        $this->assertStringNotContainsString('href="/ecdcs"', $html);
    }

    public function test_the_assessment_tabs_only_offer_permitted_areas(): void
    {
        $tabs = AssessmentTab::availableFor($this->userWithRole(Role::Teacher));
        $labels = array_map(fn (AssessmentTab $tab) => $tab->label(), $tabs);

        $this->assertSame(['Term Exam', 'Diagnostic Test', 'Summative Test', 'ECDC'], $labels);

        $tabs = AssessmentTab::availableFor($this->userWithRole(Role::SchoolHead));
        $labels = array_map(fn (AssessmentTab $tab) => $tab->label(), $tabs);

        $this->assertSame(['ECDC'], $labels);
    }

    public function test_every_permission_used_by_a_route_exists_in_the_matrix(): void
    {
        $known = RolePermissionMatrix::all();
        $used = [];

        foreach (Route::getRoutes() as $route) {
            foreach ($route->gatherMiddleware() as $middleware) {
                if (! is_string($middleware) || ! str_starts_with($middleware, 'permission:')) {
                    continue;
                }

                // A single middleware may list several permissions, either
                // comma separated (any of) or pipe separated (all of).
                foreach (preg_split('/[|,]/', Str::after($middleware, 'permission:')) as $permission) {
                    $permission = trim($permission);

                    if ($permission !== '') {
                        $used[] = $permission;
                    }
                }
            }
        }

        $this->assertNotEmpty($used);

        foreach (array_unique($used) as $permission) {
            $this->assertContains($permission, $known, "Route permission [{$permission}] is not declared in the matrix.");
        }
    }

    /**
     * The exact guards expected on the routes affected by the leaked groups,
     * asserted as a complete set so that both a leaked parent group and a
     * dropped guard are reported.
     */
    public function test_a_module_route_is_guarded_by_its_own_permission_only(): void
    {
        /** @var array<string, array{0: string, 1: string, 2: list<string>}> $expectations */
        $expectations = [
            'reports index' => ['GET', '/reports', ['reports.view']],
            'reports generate' => ['POST', '/reports/generate', ['reports.generate']],
            'item banks' => ['GET', '/item_banks', ['item_banks.view']],
            'student answers upload' => ['POST', '/student_answers/upload', ['assessments.upload']],
            'term exams index' => ['GET', '/term-exams', ['term_exams.view']],
            'term exams upload' => ['POST', '/term-exams/upload', ['term_exams.upload']],
            'diagnostics index' => ['GET', '/diagnostics', ['diagnostics.view']],
            'diagnostics upload' => ['POST', '/diagnostics/upload', ['diagnostics.upload']],
            'summatives index' => ['GET', '/summatives', ['summatives.view']],
            'summatives upload' => ['POST', '/summatives/upload', ['summatives.upload']],
            'students index' => ['GET', '/students', ['students.view']],
            'student store' => ['POST', '/students/store', ['students.manage']],
            'student update' => ['POST', '/students/update', ['students.manage']],
            'student upload' => ['POST', '/students/upload', ['students.upload']],
            'sections index' => ['GET', '/sections', ['sections.view']],
            'sections store' => ['POST', '/sections/store', ['sections.manage']],
            'classrooms index' => ['GET', '/classrooms', ['classrooms.view']],
            'classrooms upload' => ['POST', '/classrooms/upload', ['classrooms.upload']],
            'teacher classes upload' => ['POST', '/teacher_classes/upload', ['teacher_classes.upload']],
            'ecdc domains index' => ['GET', '/ecdc_domains', ['ecdc_domains.view']],
            'ecdc domains store' => ['POST', '/ecdc_domains/store', ['ecdc_domains.manage']],
            'ecdc competencies store' => ['POST', '/ecdc_domains/{ecdc_domain}/competencies/store', ['ecdc_domains.manage']],
            'ecdcs index' => ['GET', '/ecdcs', ['ecdcs.view']],
            'ecdcs upload' => ['POST', '/ecdcs/upload', ['ecdcs.upload']],
            'lookup area' => ['POST', '/getArea', ['api.lookups']],
            'lookup districts' => ['POST', '/getDistricts', ['api.lookups']],
            'lookup schools' => ['POST', '/getSchools', ['api.lookups']],
            'lookup student answers' => ['POST', '/getStudentAnswers', ['api.lookups']],
            'lookup ecdc result' => ['POST', '/getECDCResult', ['api.lookups']],
        ];

        foreach ($expectations as $label => [$method, $uri, $expected]) {
            $route = $this->findRegisteredRoute($method, $uri);

            $this->assertSame(
                $expected,
                $this->guardingPermissions($route),
                sprintf('[%s] is not guarded by exactly [%s] on [%s %s].', $label, implode(',', $expected), $method, $uri)
            );
        }
    }

    /**
     * Guards against the defect that produced spurious 403 responses: a
     * permission group left open in routes/web.php turns every later module
     * into its child, so a route starts demanding a permission from an unrelated
     * module on top of the correct one and users holding the correct permission
     * are still sent to /forbidden.
     *
     * Because a nested group's middleware is merged into the child route at
     * registration time, the leak is visible in gatherMiddleware() even though
     * every permission name on its own remains valid.
     */
    public function test_no_route_demands_a_permission_from_more_than_one_module(): void
    {
        foreach (Route::getRoutes() as $route) {
            $modules = [];

            foreach ($this->guardingPermissions($route) as $permission) {
                $modules[Str::before($permission, '.')] = true;
            }

            $this->assertLessThanOrEqual(
                1,
                count($modules),
                sprintf(
                    'Route [%s %s] is guarded by permissions from several modules [%s]; '.
                    'a permission group has almost certainly been left open in routes/web.php.',
                    implode('|', $route->methods()),
                    $route->uri(),
                    implode(', ', array_keys($modules))
                )
            );
        }
    }

    /**
     * The student write endpoints sat outside every permission group, so any
     * authenticated user could create or edit a student record.
     */
    public function test_student_writes_are_guarded_by_the_students_manage_permission(): void
    {
        // A user who may look students up but not manage them is refused. Before
        // the fix these endpoints sat outside every permission group and any
        // authenticated user could write a student record.
        $this->actingAs($this->userWithRole(Role::Teacher))
            ->post('/students/store')
            ->assertRedirect('/forbidden');

        $this->actingAs($this->userWithRole(Role::Teacher))
            ->post('/students/update')
            ->assertRedirect('/forbidden');
    }

    /**
     * The lookup endpoints feeding the report and assessment filters carried no
     * guard at all, exposing district, school, section, teacher and item data to
     * any authenticated user regardless of module access.
     */
    public function test_lookup_endpoints_are_guarded_by_the_api_lookups_permission(): void
    {
        // These endpoints carried no guard at all and leaked district, school,
        // section, teacher and item data to any authenticated user. The Student
        // role is the only seeded role without api.lookups.
        $this->actingAs($this->userWithRole(Role::Student))
            ->post('/getDistricts')
            ->assertRedirect('/forbidden');

        $this->actingAs($this->userWithRole(Role::Student))
            ->post('/getSchools')
            ->assertRedirect('/forbidden');
    }

    /**
     * The reports page is the screen that reported the 403. Division office roles
     * hold reports.view but only students.view, so the leaked students.manage
     * group bounced them even though they are entitled to the page.
     */
    public function test_a_reports_viewer_is_not_bounced_by_the_student_management_permission(): void
    {
        foreach ([Role::ChiefOfCID, Role::DistrictSupervisor, Role::DivisionSupervisor] as $role) {
            $response = $this->actingAs($this->userWithRole($role))->get('/reports');

            $this->assertNotSame(
                '/forbidden',
                $response->headers->get('Location'),
                sprintf('[%s] holds reports.view and must reach the reports page.', $role->value)
            );
        }
    }

    /**
     * The teacher facing assessment modules were likewise nested inside the
     * student management group, despite teachers only hold students.view.
     */
    public function test_a_teacher_reaches_the_assessment_modules_it_holds_permissions_for(): void
    {
        $teacher = $this->actingAs($this->userWithRole(Role::Teacher));

        foreach (['/term-exams', '/diagnostics', '/summatives'] as $url) {
            $response = $teacher->get($url);

            $this->assertNotSame(
                '/forbidden',
                $response->headers->get('Location'),
                sprintf('A teacher holds the view permission for [%s] and must not be bounced.', $url)
            );
        }
    }

    /**
     * A school head supervises a school; they are not an assessment author, and
     * the Assessment module screen it led to had no controller method behind it.
     */
    public function test_a_school_head_holds_no_assessment_permission(): void
    {
        $schoolHead = SpatieRole::findByName(Role::SchoolHead->value);

        foreach (['assessments.view', 'assessments.manage', 'assessments.upload'] as $permission) {
            $this->assertFalse(
                $schoolHead->hasPermissionTo($permission),
                sprintf('A school head must not hold [%s].', $permission)
            );
        }

        // The permissions the role is actually responsible for are untouched.
        foreach (['classrooms.manage', 'students.view', 'students.manage', 'teachers.view'] as $permission) {
            $this->assertTrue(
                $schoolHead->hasPermissionTo($permission),
                sprintf('A school head must still hold [%s].', $permission)
            );
        }
    }

    /**
     * The class assessment screen reads the signed in student's own record, so
     * the link is only offered to a student account even though other roles hold
     * the permission behind it.
     */
    public function test_the_class_assessment_link_is_only_offered_to_a_student(): void
    {
        $studentHtml = $this->actingAs($this->userWithRole(Role::Student))
            ->view('layouts.sidebar', [
                'page' => ['name' => 'Dashboard'],
                'classification' => Role::Student->value,
            ])->__toString();

        $this->assertStringContainsString('href="/students/class_assessments"', $studentHtml);

        foreach ([Role::Teacher, Role::SchoolHead] as $role) {
            $html = $this->actingAs($this->userWithRole($role))
                ->view('layouts.sidebar', [
                    'page' => ['name' => 'Dashboard'],
                    'classification' => $role->value,
                ])->__toString();

            $this->assertStringNotContainsString(
                'href="/students/class_assessments"',
                $html,
                sprintf('[%s] has no student record to read, so the link must be hidden.', $role->value)
            );
        }
    }

    /**
     * `/classrooms/{classroom}` was registered before `/classrooms/create`, so
     * the literal route was swallowed by the parameter and Laravel resolved the
     * string "create" as a Classroom key, answering 404 instead of the form.
     * The "Add Classroom" button was therefore dead on a page that had been
     * throwing a 500 of its own until the canManageClassrooms() call was fixed.
     */
    public function test_the_add_classroom_form_is_not_shadowed_by_the_show_route(): void
    {
        $schoolHead = $this->userWithRole(Role::SchoolHead);

        // The form is only rendered for an account that resolves to a school;
        // a school head with no supervisor row is redirected away before the
        // view is reached, which would mask the route shadowing.
        $this->linkSchoolHeadToASchool($schoolHead);

        $response = $this->actingAs($schoolHead)->get('/classrooms/create');

        $response->assertOk();
        $response->assertSee('action="/classrooms/store"', false);
    }

    private function linkSchoolHeadToASchool(User $schoolHead): void
    {
        DB::table('tbl_school_types')->insertOrIgnore(['type' => 'Elementary']);

        $schoolId = DB::table('tbl_schools')->insertGetId([
            'code' => 'SHADOW-CHECK',
            'name' => 'Shadow Check School',
            'address' => 'Somewhere',
            'school_category_id' => 1,
            'school_type_id' => DB::table('tbl_school_types')->where('type', 'Elementary')->value('id'),
            'district_id' => 1,
        ]);

        DB::table('tbl_school_supervisors')->insert([
            'user_id' => $schoolHead->id,
            'school_id' => $schoolId,
            'status' => 1,
            'email' => $schoolHead->username,
        ]);

        DB::table('tbl_academic_years')->insert([
            'from' => '2026',
            'to' => '2027',
            'is_active' => 1,
        ]);
    }

    /**
     * Guards the whole route table, not just this one module: a literal path
     * must never be shadowed by an earlier unconstrained parameter in the same
     * segment position, because that silently turns the literal route into a
     * 404 the moment the two are registered in the wrong order.
     */
    public function test_no_literal_route_is_shadowed_by_an_unconstrained_parameter(): void
    {
        $shadowed = [];

        $routesByMethod = [];

        foreach (Route::getRoutes() as $route) {
            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'], true)) {
                    continue;
                }

                $routesByMethod[$method][] = $route;
            }
        }

        foreach ($routesByMethod as $method => $routes) {
            foreach ($routes as $index => $literal) {
                $literalSegments = explode('/', $literal->uri());

                if (str_contains($literal->uri(), '{')) {
                    continue;
                }

                for ($earlier = 0; $earlier < $index; $earlier++) {
                    $candidate = $routes[$earlier];
                    $candidateSegments = explode('/', $candidate->uri());

                    if (count($candidateSegments) !== count($literalSegments)) {
                        continue;
                    }

                    foreach ($literalSegments as $position => $segment) {
                        $parameter = $candidateSegments[$position];

                        if (! str_starts_with($parameter, '{') || ! str_ends_with($parameter, '}')) {
                            continue;
                        }

                        // Only a parameter in this exact position, surrounded by
                        // otherwise identical segments, can swallow the literal.
                        $surroundingSegmentsDiffer = false;

                        foreach ($literalSegments as $other => $value) {
                            if ($other !== $position && $value !== $candidateSegments[$other]) {
                                $surroundingSegmentsDiffer = true;

                                break;
                            }
                        }

                        if ($surroundingSegmentsDiffer) {
                            continue;
                        }

                        // A parameter with a `where` constraint cannot swallow a
                        // literal that fails that constraint.
                        if ($candidate->wheres !== [] && array_key_exists(trim($parameter, '{}'), $candidate->wheres)) {
                            continue;
                        }

                        $shadowed[] = sprintf(
                            '[%s %s] is swallowed by the earlier [%s %s].',
                            $method,
                            $literal->uri(),
                            $method,
                            $candidate->uri()
                        );
                    }
                }
            }
        }

        $this->assertSame([], $shadowed, implode(PHP_EOL, $shadowed));
    }

    /**
     * The permission names a route is guarded by, including any inherited from
     * an enclosing group, sorted for stable comparison.
     *
     * @return list<string>
     */
    private function guardingPermissions(RouteDefinition $route): array
    {
        $permissions = [];

        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware) || ! str_starts_with($middleware, 'permission:')) {
                continue;
            }

            foreach (preg_split('/[|,]/', Str::after($middleware, 'permission:')) as $permission) {
                $permission = trim($permission);

                if ($permission !== '') {
                    $permissions[] = $permission;
                }
            }
        }

        $permissions = array_values(array_unique($permissions));
        sort($permissions);

        return $permissions;
    }

    private function findRegisteredRoute(string $method, string $uri): RouteDefinition
    {
        $uri = trim($uri, '/');

        $route = collect(Route::getRoutes())->first(
            fn (RouteDefinition $registered): bool => trim($registered->uri(), '/') === $uri
                && in_array($method, $registered->methods(), true)
        );

        $this->assertNotNull($route, "No [{$method}] route is registered for [{$uri}].");

        return $route;
    }
}
