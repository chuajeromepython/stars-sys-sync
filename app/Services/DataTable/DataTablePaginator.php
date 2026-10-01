<?php

namespace App\Services\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DataTablePaginator
{
    /**
     * Upper bound applied when DataTables requests every row (`length = -1`).
     */
    public const MAX_LENGTH = 100;

    /**
     * Page size used when the request does not provide a usable length.
     */
    private const DEFAULT_LENGTH = 10;

    /**
     * Build a server-side processed DataTables response.
     *
     * Each entry of $columns describes one column of the table:
     *  - data:       the key returned in the row payload (and the DataTables column data)
     *  - source:     the row attribute the value is read from, defaults to data
     *  - column:     the SQL expression used for searching/ordering, defaults to data
     *  - searchable: whether the column participates in the global search
     *  - orderable:  whether the column may be sorted
     *  - render:     optional callable(Builder $row, string $value): string used to
     *                produce a cell value, typically pre-escaped HTML
     *
     * $filters describes the toolbar selects the listing offers, keyed by the
     * key the select posts:
     *  - options: callable(): list<array{label: string, value: string}>
     *  - apply:   callable(Builder $query, string $value): void
     *
     * Only the keys declared here can ever reach the query, so a request can
     * never filter by something the tab does not offer.
     *
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, array<string, callable>>  $filters
     * @return array{draw: int, recordsTotal: int, recordsFiltered: int, data: array<int, array<string, mixed>>}
     */
    public function paginate(Builder $query, Request $request, array $columns, array $filters = []): array
    {
        $recordsTotal = (clone $query)->count();

        $query = $this->applySearch(clone $query, $request, $columns);
        $query = $this->applySelectFilters($query, $request, $filters);
        $query = $this->applyOrder($query, $request, $columns);

        $recordsFiltered = (clone $query)->count();

        $length = $this->resolveLength($request->input('length'));
        $start = max(0, (int) $request->input('start', 0));

        $rows = $query->forPage(intdiv($start, $length) + 1, $length)->get();

        return [
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows->map(fn ($row) => $this->mapRow($row, $columns))->values()->all(),
        ];
    }

    /**
     * Constrain the query using the DataTables global search term.
     *
     * @param  array<int, array{data: string, column?: string, searchable?: bool, orderable?: bool, render?: callable}>  $columns
     */
    private function applySearch(Builder $query, Request $request, array $columns): Builder
    {
        $term = trim((string) $request->input('search.value', ''));

        if ($term === '') {
            return $query;
        }

        $term = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(function (Builder $group) use ($request, $columns, $term): void {
            foreach ($this->searchableColumns($request, $columns) as $expression) {
                $group->orWhere($expression, 'like', $term);
            }
        });
    }

    /**
     * Constrain the query using the toolbar select filters.
     *
     * A selection arrives as filters[<key>] with one value, because the toolbar
     * renders a single selection select per filter. Keys the listing does not
     * declare are ignored, so nothing reaches the query but a filter the tab
     * itself defined.
     *
     * @param  array<string, array<string, callable>>  $filters
     */
    private function applySelectFilters(Builder $query, Request $request, array $filters): Builder
    {
        $selected = $request->input('filters', []);

        if ($filters === [] || ! is_array($selected)) {
            return $query;
        }

        foreach ($filters as $key => $definition) {
            $value = $selected[$key] ?? null;

            if (! is_string($value) || $value === '' || ! isset($definition['apply'])) {
                continue;
            }

            ($definition['apply'])($query, $value);
        }

        return $query;
    }

    /**
     * Apply the requested column ordering, ignoring unknown or non-orderable columns.
     *
     * @param  array<int, array{data: string, column?: string, searchable?: bool, orderable?: bool, render?: callable}>  $columns
     */
    private function applyOrder(Builder $query, Request $request, array $columns): Builder
    {
        $index = (int) $request->input('order.0.column', -1);

        if (! array_key_exists($index, $columns)) {
            return $query;
        }

        $column = $columns[$index];

        if (($column['orderable'] ?? true) === false) {
            return $query;
        }

        $direction = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($this->expression($column), $direction);
    }

    /**
     * Build the row payload keyed by each column's data name.
     *
     * @param  array<int, array{data: string, column?: string, searchable?: bool, orderable?: bool, render?: callable}>  $columns
     * @return array<string, mixed>
     */
    private function mapRow(mixed $row, array $columns): array
    {
        $data = [];

        foreach ($columns as $column) {
            $key = $column['data'];
            $source = $column['source'] ?? $column['data'];
            $value = $row->{$source} ?? null;
            $value = $value === null ? '' : (string) $value;

            $data[$key] = isset($column['render'])
                ? (string) ($column['render'])($row, $value)
                : $value;
        }

        return $data;
    }

    /**
     * Resolve the SQL expressions that may participate in the global search.
     *
     * @param  array<int, array{data: string, column?: string, searchable?: bool, orderable?: bool, render?: callable}>  $columns
     * @return array<int, string>
     */
    private function searchableColumns(Request $request, array $columns): array
    {
        $requested = $request->input('columns', []);

        $expressions = [];

        foreach ($columns as $index => $column) {
            if (($column['searchable'] ?? true) === false) {
                continue;
            }

            $clientColumn = is_array($requested) ? ($requested[$index] ?? null) : null;

            if (is_array($clientColumn) && isset($clientColumn['searchable'])
                && filter_var($clientColumn['searchable'], FILTER_VALIDATE_BOOLEAN) === false) {
                continue;
            }

            $expressions[] = $this->expression($column);
        }

        return $expressions;
    }

    /**
     * @param  array{data: string, column?: string}  $column
     */
    private function expression(array $column): string
    {
        return $column['column'] ?? $column['data'];
    }

    /**
     * Normalise the requested page size, capping "show all" requests.
     */
    private function resolveLength(mixed $length): int
    {
        $length = (int) $length;

        if ($length <= 0) {
            return self::DEFAULT_LENGTH;
        }

        return min($length, self::MAX_LENGTH);
    }
}
