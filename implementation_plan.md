# Implementation Plan

## Overview
Refine the User Management module (Users / Roles / Permissions tabs) by adding a real filtering capability to every tab, removing the Classification column from the users table, and moving the whole application from the AdminLTE3-bundled DataTables 1.11.4 to the DataTables 3.1.2 combined build already present in `public/vendor/DataTables`.

The module already has a server-side skeleton (`UserManagementDataTable` + `DataTablePaginator` + `/user-management/data` + `public/js/user-management.js`). This work extends that skeleton: filters are expressed with DataTables 3 **ColumnControl `searchList`** and **SearchBuilder**, both of which post structured criteria to the same endpoint, and the paginator learns to translate those criteria into query constraints.

Decisions confirmed with the user:
- DataTables 3.1.2 is wired in **globally** in `resources/views/layouts/master.blade.php`, replacing the AdminLTE3 DataTables assets so two DataTables never register on one page.
- Filters are rendered by **ColumnControl / SearchBuilder** in the table header, mapping to the same server-side params.
- The users tab filters by **role** and **area**; area is resolved for real from each user's linked profile table (division / district / school), as the legacy listing did.
- The roles tab gets a **Users** column that doubles as the user filter; the permissions tab gets **Module** + **Roles** columns, where Roles is the role filter.

---

## Types

### New enum: `app/Enums/UserManagementFilter.php`
Backed string enum describing the filterable dimensions per tab, used to validate incoming request criteria against a known allow-list (never trust client-supplied column names).

```php
enum UserManagementFilter: string
{
    case Role = 'roles';
    case Area = 'area';
    case User = 'users';
    case Module = 'module';

    public static function forTab(string $tab): array;          // list<self>
    public static function tryFromColumn(string $tab, string $data): ?self;
    public function tab(): string;                               // 'users' | 'roles' | 'permissions'
}
```

### Extended column definition shape (`array<string, mixed>` per column)
The `columns` array returned by `UserManagementDataTable::resolve()` gains three optional keys, consumed by `DataTablePaginator`:

| key | type | purpose |
|---|---|---|
| `filterOptions` | `callable(): list<array{label: string, value: string}>` | Distinct values offered by the ColumnControl `searchList` dropdown and by SearchBuilder's `=` condition. Returned to the client in the JSON response. |
| `applyListFilter` | `callable(Builder $query, list<string> $values): void` | Applies a `searchList` selection (always an IN / OR-of-equals). |
| `applyCriteria` | `callable(Builder $query, string $condition, list<string> $values): void` | Applies a single SearchBuilder criterion (`=`, `!=`, `contains`, `notContains`, `starts`, `ends`, `empty`, `notEmpty`, `in`, `notIn`). |

### Request shapes the paginator must understand
- ColumnControl: `columns[<i>][columnControl][list][<value>] = "true"`
- SearchBuilder: `searchBuilder[<g>][logic] = AND|OR`, `searchBuilder[<g>][criteria][<c>][data|origData|type|condition]`, `[value][]`, `[value1]`, `[value2]`

### Response additions
```php
array{
  draw: int, recordsTotal: int, recordsFiltered: int,
  data: array<int, array<string, mixed>>,
  columnControl: array<string, list<array{label: string, value: string}>>,
  searchBuilder: array{options: array<string, list<array{label: string, value: string}>>},
}
```

---

## Files

### New files
| Path | Purpose |
|---|---|
| `app/Enums/UserManagementFilter.php` | Allow-list of filterable dimensions per tab. |
| `app/Services/DataTable/UserAreaQuery.php` | Builds the `user_areas` derived table (UNION of every profile table joined to its division/district/school) and exposes `join()` + `options()`. |
| `tests/Feature/UserManagementFilterTest.php` | Feature tests for every filter on every tab, plus the removed classification column. |

### Modified files
| Path | Changes |

---

## Functions

### `app/Services/DataTable/DataTablePaginator.php`
| Function | Change |
|---|---|
| `paginate(Builder, Request, array): array` | **Modified** — insert `applyListFilters()` + `applySearchBuilder()` between `applySearch()` and `applyOrder()`; add `columnControl` and `searchBuilder` keys. |
| `applyListFilters` | **New** — read `columns[i].columnControl.list`; invoke `applyListFilter` when present. |
| `applySearchBuilder` | **New** — flatten `searchBuilder[*].criteria[*]`, resolve each criterion to a known column **by position in the server-side `$columns` array**, honour group `AND`/`OR`. Unknown columns are skipped, never interpolated. |
| `filterOptions`, `criteriaMap` | **New** — collect filter options and normalise `value`/`value1`/`value2` into a flat list of strings. |
| `searchableColumns`, `applyOrder`, `mapRow`, `expression`, `resolveLength` | Unchanged. |

### `app/Services/DataTable/UserAreaQuery.php` (new)
| Function | Signature | Purpose |
|---|---|---|
| `subQuery` | `private function subQuery(): Builder` | `(SELECT user_id, MIN(name) AS area FROM ( …UNION ALL… ) x GROUP BY user_id)`. Branches: division-level tables → `tbl_divisions`; `tbl_district_supervisors` → `tbl_districts`; school-level tables → `tbl_schools`. Every branch filters `deleted_at IS NULL`. |
| `join` | `public function join(Builder $query, string $alias = 'ua'): Builder` | `leftJoinSub(...)` + `addSelect("$alias.area as area")`. |
| `options` | `public function options(): array` | Distinct, sorted area names as `{label, value}`. |

### `app/Services/DataTable/UserManagementDataTable.php`
| Function | Change |
|---|---|
| `users(Request)` | Drop the `classification` column; join the area; make `area` orderable/searchable with `column => 'ua.area'`; wire `roles` + `area` filters. |
| `scopeUsers()` | Drop the `classification` request key; `area` now matches `ua.area`. |
| `roles()` | Add a `users` members column (badges, up to 5 + `+N more`) with `filterOptions` (all users) and `applyListFilter`/`applyCriteria` on `whereHas('users')`. |
| `permissions()` | `roles` column switches from `roles_count` to rendered role names; `module` column gains filter options and a `name like '<module>.%'` filter. |
| `roleBadges()` | Memoise per request instead of `User::find()` per row. |
| `userActions()`, `roleActions()`, `permissionActions()` | Unchanged. |

---

## Classes

### New
- **`App\Enums\UserManagementFilter`** — backed string enum, no inheritance.
- **`App\Services\DataTable\UserAreaQuery`** — final class, no inheritance, constructor-less.

### Modified
- **`App\Services\DataTable\DataTablePaginator`** — filter plumbing; PHPDoc array shapes extended.
- **`App\Services\DataTable\UserManagementDataTable`** — gains `public function __construct(private UserAreaQuery $areas) {}`.
- **`App\Http\Controllers\RoleController`** — `data()` forwards the paginator payload verbatim; no signature change.
- **`App\Http\Controllers\UserController`** — `datatable()` removed; `index()` no longer computes `$classifications`.

### Removed
- `App\Http\Controllers\UserController::datatable()` — no remaining callers.

---

## Dependencies
No new Composer or npm packages. `public/vendor/DataTables/*` is already vendored and must not be deleted or regenerated.

Two runtime notes: DataTables 3.1.2 requires jQuery >= 1.7 and the bundle ships its own Bootstrap 4 integration (which is what AdminLTE3 uses), so the CSS swap is safe. The one real breakage is the removal of the legacy `fn*` DataTable API, which is why the five ECDC / item bank / trails / report files are in scope.

---

## Testing

### New: `tests/Feature/UserManagementFilterTest.php`
`RefreshDatabase` + `InteractsWithRbac`, helpers modelled on `ListingDataTableTest`.

1. Users tab no longer returns a `classification` column / header.
2. Users tab resolves `area` from the user's profile.
3. Users tab filters by role.
4. Users tab filters by area.
5. Users tab exposes area + role filter options.
6. Roles tab filters by user.
7. Roles tab exposes its members column.
8. Permissions tab filters by role.
9. Permissions tab filters by module.
10. SearchBuilder criteria narrow the result set.
11. An unknown filter column is ignored, not injected (injection guard).
12. Filters still require the tab permission (403).

### Modified: `tests/Feature/RoleManagementTest.php`
- Existing assertions on `data.0.username` / `action` / `roles` keep passing (additive response keys).
- Add an assertion that the roles shell carries the new `<th>Users</th>`.

### Validation
```
vendor/bin/phpunit --filter UserManagementFilterTest
vendor/bin/phpunit --filter RoleManagementTest
vendor/bin/phpunit --filter ListingDataTableTest
vendor/bin/phpunit --filter DashboardRecordsServerSideTest
vendor/bin/phpunit --filter RoleBasedAccessControlTest
vendor/bin/phpunit
vendor/bin/pint --dirty --format agent
```
Baseline: `RoleManagementTest` = 41 tests / 133 assertions, all green.

---

## Implementation Order

1. `app/Enums/UserManagementFilter.php`
2. `UserAreaQuery`
3. `DataTablePaginator` filter plumbing
4. `UserManagementDataTable` (users → roles → permissions)
5. `RoleController` DI verification
6. Blade headers
7. `public/js/user-management.js`
8. `layouts/master.blade.php` asset swap
9. Legacy `fn*` call sites
10. Dead-code removal
11. `tests/Feature/UserManagementFilterTest.php`
12. `RoleManagementTest` touch-ups, full suite, Pint

Steps 1–5 are pure backend and independently testable; 6–7 are the visible tab work; 8–9 are the cross-cutting library migration and carry the most regression risk, which is why they are isolated.

|---|---|
| `app/Services/DataTable/DataTablePaginator.php` | Add `applyListFilters()`, `applySearchBuilder()`, and the two new response keys. |
| `app/Services/DataTable/UserManagementDataTable.php` | Users: drop `classification`, make `area` real, add role + area filters. Roles: add a members column + user filter. Permissions: roles names column + module filter. |
| `resources/views/users/index.blade.php` | Remove `<th>Classification</th>`. |
| `resources/views/roles/index.blade.php` | Add `<th>Users</th>`. |
| `public/js/user-management.js` | Enable `searchList` ColumnControl + SearchBuilder, update per-tab column lists. |
| `resources/views/layouts/master.blade.php` | Replace AdminLTE3 DataTables assets with `/vendor/DataTables/datatables.min.css` + `datatables.min.js`. |
| `public/js/ecdcs.js`, `public/js/item_banks.js`, `public/js/trails.js`, `public/js/reports/generate.js`, `resources/views/ecdc/show.blade.php` | DT3 removed the legacy `fn*` API: `fnDestroy()` → `.destroy()`, `fnClearTable()` → `.clear()`, `.dataTable()` → `.DataTable()`. |
| `app/Http/Controllers/UserController.php` | Remove the dead `datatable()` method and its unused call in `index()`. |

### Deleted files
- `resources/views/users/search.blade.php` (not included by any view; superseded by the tab filters).

### Not modified
`routes/web.php`, `app/Services/Rbac/*`, `config/permission.php`, `database/**`, `composer.json`.
