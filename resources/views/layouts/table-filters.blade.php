@php
    /**
     * The toolbar filter selects of a listing.
     *
     * Rendered by a controller (see UserManagementDataTable::filterDefinitionsFor)
     * as a list of ['key', 'label', 'placeholder', 'options'] and moved into the
     * DataTables toolbar by the page script, which is why the wrapper carries the
     * id the layout looks for.
     */
    $filters = $filters ?? [];
@endphp

@if (count($filters) > 0)
    <div class="dt-filter-bar" id="dt_filters">
        @foreach ($filters as $filter)
            <label class="dt-filter">
                <span class="dt-filter-label">{{ $filter['label'] }}</span>
                <select class="form-control form-control-sm dt-filter-select"
                    data-filter="{{ $filter['key'] }}"
                    aria-label="{{ $filter['label'] }}">
                    <option value="">{{ $filter['placeholder'] }}</option>
                    @foreach ($filter['options'] as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach

        <button type="button" class="btn btn-sm btn-link dt-filter-clear" id="dt_filter_clear" hidden>
            <i class="fa fa-times mr-1"></i> Clear filters
        </button>
    </div>
@endif
