# Implementation Plan

Status: **implemented and verified** - full suite green (155 tests, 1190 assertions) and verified against the live `stars` database.

## Overview

Fix the defects blocking the Teacher and School Head accounts by replacing the legacy `SchoolSupervisor::where('user_id', ...)` school lookup with the existing `CustomFunction::resolveSchoolIdForUser()` everywhere, scoping the dashboard directory and its statistics to what each role is actually entitled to, making the "Class Assessment" page a student-only screen that students can actually open, and deleting the dead `/assessments` module whose route targets a controller method that does not exist.

Every root cause below was reproduced against the live `stars` database, not inferred from reading alone.

## Root causes

| # | Symptom | Verified root cause |
|---|---|---|
| 1 | `ClassroomController.php:65` Undefined variable | Line 65 called `$canManageClassrooms()` as a bare function. The method is declared `private function canManageClassrooms(): bool` at line 36. 500 for every role. |
| 2 | `StudentController.php:595` Attempt to read `id` on null | `/students/class_assessments` is a student-only page, but the sidebar offered the link to any role holding `class_assessments.view`. The route was also guarded by `students.view`, which the Student role does not hold, so a real student got `302 -> /forbidden`. Broken for everyone. |
| 3 | Student / Teacher / Department Head tables empty | Those controllers resolved the school from `tbl_school_supervisors` only. A Teacher or Department Head has no row there, so `school_id` was null, `where('school_id', null)` matched nothing. |
| 4 | Dashboard directory unfiltered | `DashboardDataTable` had no scoping whatsoever on any of its three queries. |
| 5 | Dashboard statistics global | `DashboardController::index()` counted whole tables. |
| 6 | School Head sees 0 classrooms | `CustomFunction::getClassrooms()` delegated to `getClassroomsByTeacherUserId()`, which returns `[]` when the user has no `tbl_teachers` row. A School Head never has one. |
| 7 | School Head has assessments permission | `RolePermissionMatrix` granted `assessments.view` to `SchoolHead`, and `routes/web.php` mapped it to `AssessmentController::index()`, a method that does not exist. The permission could only ever lead to a crash. |

## Types

No new types. Scoping is data-driven from the roles already modelled in `App\Enums\Role`, mirroring the existing `App\Support\AssessmentTab` and `App\Support\ReportScope` pattern.

New public methods on `App\Services\DataTable\DashboardDataTable`:

```php
/**
 * @return list<'teachers'|'students'|'classrooms'>
 */
public function typesFor(?Authenticatable $user = null): array;

/**
 * @return array{teacher_id: int|null, school_id: int|null}
 */
public function scope(?Authenticatable $user = null): array;

public function allows(?string $type, ?Authenticatable $user = null): bool;
```

A Teacher gets `['students', 'classrooms']`; a School Head, Department Head or division office role gets all three; a Student gets `[]` and the directory is hidden.

## Files

### New files
| Path | Purpose |
|---|---|
| `tests/Feature/SchoolScopedListingTest.php` | Covers fixes 1, 3, 6: scoping of Students, Teachers, Department Heads and Classrooms per role. 10 tests. |

### Modified files
| Path | Changes |
|---|---|
| `app/Http/Controllers/ClassroomController.php` | `$canManageClassrooms()` -> `$this->canManageClassrooms()`. |
| `app/Models/CustomFunction.php` | Added `getClassroomsForSchool(int $schoolId)`; `getClassrooms()` now branches on whether the user holds a `Teacher` row. |
| `app/Http/Controllers/StudentController.php` | All 5 school lookups -> `resolveSchoolIdForUser()`; added null and missing-record guards to `studentsClassAssessment()` and `studentsClassAssessmentShow()`. |
| `app/Http/Controllers/TeacherController.php` | All 5 school lookups -> `resolveSchoolIdForUser()`. |
| `app/Http/Controllers/DepartmentHeadController.php` | All 3 school lookups -> `resolveSchoolIdForUser()`. |
| `app/Services/DataTable/DashboardDataTable.php` | `scope()`, `typesFor()`, `allows()`; all three queries scoped. |
| `app/Http/Controllers/DashboardController.php` | Role-aware `statistics()`; `records()` enforces `allows()`. |
| `resources/views/layouts/dashboard.blade.php` | Stat cards from `$statistics`; record-type select from `$recordTypes`; the whole directory hidden for a student. |
| `resources/views/layouts/sidebar.blade.php` | Class Assessment link gated to a student account. |
| `app/Services/Rbac/RolePermissionMatrix.php` | Removed `assessments.view` from `Role::SchoolHead`. |
| `routes/web.php` | Dead `/assessments` routes removed; the two `/students/class_assessments` routes moved into the `class_assessments.view` group. |
| `tests/Feature/DashboardRecordsServerSideTest.php` | Retargeted from Teacher to School Head; scoping assertions added. |
| `tests/Feature/RoleBasedAccessControlTest.php` | Route-guard expectations updated; permission and sidebar assertions added. |
| `tests/Feature/CustomFunctionClassroomsTest.php` | School Head (non-teacher) and cross-school cases added. |

### Deleted files
- `resources/views/assessments/index.blade.php`, `resources/views/assessments/upload.blade.php`, `public/js/assessments.js`
- `app/Http/Controllers/AssessmentController.php` - its only method, `show()`, was routed nowhere, and `index` / `store` / `update` / `destroy` / `upload` did not exist.

`assessments.manage` and `assessments.upload` remain in the matrix for Teacher and Department Head. The live endpoints `/questions/{id}/update-answer-key`, `/student_answers/upload` and `/student_answers/batch_update` were preserved and re-grouped.

### Not modified
`composer.json`, `database/**`, `config/**`, `app/Services/DataTable/DataTablePaginator.php`, `app/Services/DataTable/UserManagementDataTable.php`, `public/vendor/**`.

## Functions

### `app/Models/CustomFunction.php`
| Function | Change |
|---|---|
| `getClassroomsForSchool(int $schoolId): array` | **New.** Every classroom of a school for the active AY, grouped by grade level, same shape and the same eager loading as the teacher variant, plus a `withCount('teacherClasses')`. No teacher filter, so every room is included. |
| `getClassrooms(): array` | **Modified.** Now branches: the user has a `Teacher` row -> teacher-scoped (unchanged behaviour); otherwise resolve the school and return `getClassroomsForSchool()`; a null school still returns `[]`. |
| `getClassroomsByTeacherUserId($id)` | **Unchanged.** Still teacher-only, still used by the classroom-sync API, still covered by the 12-query budget test. |
| `resolveSchoolIdForUser()` | **Unchanged.** The correct helper already existed; the controllers simply were not using it. |

### `app/Http/Controllers/ClassroomController.php`
| Function | Change |
|---|---|
| `index()` | `$canManageClassrooms()` -> `$this->canManageClassrooms()`. |

### `app/Http/Controllers/StudentController.php`
| Function | Change |
|---|---|
| `index()`, `data()`, `create()`, `edit()` | School lookup -> `resolveSchoolIdForUser()`; the dead `SchoolSupervisor` import removed. |
| `studentsClassAssessment()` | `abort_if($student === null, 403)` replaces the fatal null deref; also 404 when there is no active academic year. |
| `studentsClassAssessmentShow()` | Same 403 guard, 404 for a missing assessment, and `$result = $get_result[$student->id] ?? null` plus a 404 instead of an undefined-index fatal. The per-item answer read is now `?? 0`. |

### `app/Http/Controllers/TeacherController.php` / `DepartmentHeadController.php`
Every occurrence of the legacy lookup replaced with `resolveSchoolIdForUser()`.

### `app/Services/DataTable/DashboardDataTable.php`
| Function | Change |
|---|---|
| `typesFor()`, `scope()`, `allows()` | **New.** |
| `resolve(string $type, ?Authenticatable $user = null)` | **Modified.** Resolves the scope and passes it to the three builders. |
| `teachers(array $scope)` | **Modified.** Filters `tbl_teachers.school_id` for a school-scoped role. |
| `students(array $scope)` | **Modified.** A teacher gets a `whereExists` against `tbl_student_classrooms` so a student enrolled in two of their classrooms is still listed once; a school-scoped role gets `tbl_students.school_id`. |
| `classrooms(array $scope)` | **Modified.** Teacher -> `whereIn` their assigned classroom ids; school-scoped role -> `tbl_classrooms.school_id`. |
| `teacherClassroomIds(?int)` | **New private.** Null means "no teacher scope", which callers read as "do not narrow". |

### `app/Http/Controllers/DashboardController.php`
| Function | Change |
|---|---|
| `index(DashboardDataTable $dashboardDataTable)` | Passes `$statistics` and `$recordTypes` to the view instead of six global counts. |
| `statistics(?AcademicYear): array` | **New.** Role-aware card list. |
| `teacherClassCount(int $teacherId, bool $advisory): int` | **New.** |
| `records()` | A type the role is not offered resolves to the empty payload, so a hand-crafted query string cannot widen the directory. |

## Classes

### Modified
- **`App\Services\DataTable\DashboardDataTable`** - gains three public methods and one private; all three query builders take a scope argument. No inheritance.
- **`App\Http\Controllers\DashboardController`** - gains `statistics()` and `teacherClassCount()`; `index()` now takes a dependency. No inheritance.
- **`App\Models\CustomFunction`** - gains `getClassroomsForSchool()`; `getClassrooms()` gains a branch. No inheritance.
- **`App\Services\Rbac\RolePermissionMatrix`** - `final`; one array literal edited.

### Removed
- `App\Http\Controllers\AssessmentController` - the whole module was unreachable.

## Dashboard statistics

| Role | Cards |
|---|---|
| Teacher | **Advisory** (distinct classrooms where `advisory = 1`), **Subject Class** (`advisory = 0`), **Students** (distinct students enrolled in those classrooms), **Classrooms** (distinct) |
| School Head / Department Head | **Classrooms**, **Teachers**, **Students**, **Sections** - all scoped to their school |
| Division office | Classrooms, Teachers, Students, Sections (unchanged) |
| Student | none - the section is hidden |

## Dependencies

None. No new Composer or npm packages, no schema changes, no migrations. `resolveSchoolIdForUser()`, `AcademicYear::active()`, `filterGradeLevel()` and the DataTables 3.1.2 bundle in `public/vendor/DataTables` were all already present and in use.

## Testing

### New: `tests/Feature/SchoolScopedListingTest.php`
`RefreshDatabase` + `InteractsWithRbac`, two schools with teachers, students, classrooms and department heads in each.

1. `/classrooms` renders 200 for a School Head (regression for the L65 fatal).
2. `/classrooms` renders 200 for a Teacher.
3. A School Head sees their own school's rooms and not another's.
4. A Teacher sees only rooms assigned via `tbl_teacher_classes`.
5. A Teacher's `/students/data` returns rows (regression for fix 3).
6. A School Head's student listing is school scoped.
7. A School Head's teacher listing is school scoped.
8. The department head listing is school scoped, not empty.
9. A role with no school record sees no rows rather than everything.
10. A department head resolves their school.

### Modified: `tests/Feature/DashboardRecordsServerSideTest.php`
Existing cases retargeted from Teacher to School Head, with the row counts adjusted to the new fixture helper. Added:
- `type=teachers`, `type=students`, `type=classrooms` for a School Head are each school scoped.
- A Teacher requesting `type=teachers` gets the empty payload.
- A Teacher's students are limited to their own classrooms.
- A Teacher's classrooms are limited to their assignment.
- The record-type select omits `Teachers` for a Teacher and includes it for a School Head.

### Modified: `tests/Feature/RoleBasedAccessControlTest.php`
- The dead `assessments` route expectations are replaced with the still-live `/student_answers/upload`.
- Asserts a School Head holds no `assessments.*` and still holds `classrooms.manage`, `students.*`, `teachers.*`.
- Asserts the Class Assessment link renders for a Student and is hidden for a Teacher and a School Head.

### Modified: `tests/Feature/CustomFunctionClassroomsTest.php`
Adds a School Head (non-teacher) case and a cross-school case, keeping the existing 12-query assertion for the teacher path intact.

### Validation
```
vendor/bin/phpunit --filter=SchoolScopedListingTest
vendor/bin/phpunit --filter=DashboardRecordsServerSideTest
vendor/bin/phpunit --filter=RoleBasedAccessControlTest
vendor/bin/phpunit --filter=CustomFunctionClassroomsTest
vendor/bin/phpunit
vendor/bin/pint --dirty --format agent
```

Result: 155 tests, 1190 assertions, all passing.

## Implementation Order

1. `ClassroomController` L65 - one character, unblocks the Classroom page for every role.
2. `getClassroomsForSchool()` plus the `getClassrooms()` branch - the School Head must see classrooms before anything can be verified against them.
3. Swap the school lookup in the listing controllers and drop the dead imports.
4. `DashboardDataTable` - `typesFor()`, `scope()`, `allows()` and the three scoped queries.
5. `DashboardController::statistics()` plus `dashboard.blade.php`.
6. Class Assessment - controller guards, sidebar condition, route move out of `students.view`.
7. `RolePermissionMatrix` - drop `assessments.view` from School Head.
8. Delete the dead `/assessments` route, view and JS, and the unreachable controller.
9. `SchoolScopedListingTest`, then update the three existing test files.
10. Full suite, Pint, then re-provision RBAC on the live database.

Steps 1-3 are the crash and empty-table fixes and are independently verifiable. Steps 4-5 are the dashboard scoping and are the largest behavioural change. Step 8 is isolated and reversible.

## Live verification

- The `assessments` route is gone; `students/class_assessments` is registered.
- RBAC re-provisioned against the live database: 74 permissions across 12 roles, 231 users synced. A School Head now holds no `assessments.view`, `assessments.manage` or `assessments.upload`, and still holds `classrooms.manage`, `students.manage` and `teachers.*`. A Teacher still holds `assessments.manage`.
- School Head (user 233, school 312): directory returns `teachers=10`, `students=22`, `classrooms=3`; record types = all three.
- Teacher (user 413, school 312): record types = `students,classrooms`; `teachers` is not offered; students and classrooms are scoped to their own school and assignment.

## Open items

- `tbl_department_heads` has 0 rows and no account is classified Department Head, so that table remains empty. The scoping fix is verified through test fixtures rather than live data. Seeding department heads is a data task, not a code one.

## Follow-up fix: "Add Classroom" route not found

Reported after the main work: `GET /classrooms/create` returned 404.

**Nothing was deleted or mis-edited.** `resources/views/classrooms/create.blade.php` and the
`/classrooms/create` route both exist and are untouched. The cause is route ordering, and it was
hidden behind the `$canManageClassrooms` fatal: while the Classroom listing threw a 500 nobody
could reach the button, so the shadowed route below it was never observable.

`/classrooms/{classroom}` was registered *before* `/classrooms/create`, and the former was
unconstrained. Laravel matches in registration order, so `/classrooms/create` matched
`/classrooms/{classroom}` with the string `create` as the route key, and implicit model binding
tried to find a Classroom whose key is `create` -> 404.

Fix, in `routes/web.php`:

```php
Route::get('/classrooms/{classroom}', [ClassroomController::class, 'show'])
    ->whereNumber('classroom');
```

`Classroom` has no `getRouteKeyName()` override, so the key is the numeric primary key; the
constraint is correct rather than merely a workaround.

A scan of every non-API route in the file found no other literal path swallowed by an earlier
unconstrained parameter.

### Tests
Two added to `tests/Feature/RoleBasedAccessControlTest.php`:
1. `test_the_add_classroom_form_is_not_shadowed_by_the_show_route` - renders the form as a school head and asserts the store action is present.
2. `test_no_literal_route_is_shadowed_by_an_unconstrained_parameter` - scans the whole route table for the same class of defect.

Both were verified to fail with the fix reverted and pass with it restored.
Suite after this fix: 157 tests, 1193 assertions.
