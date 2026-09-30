// Shared server side DataTable for the three user management tabs.
// The active tab is set by each view as `user_management_tab`. Filtering is done
// by the toolbar selects the view renders (layouts.table-filters): their
// `data-filter` keys are posted as `filters[<key>]`, and the endpoint only
// honours the keys the tab declares.

$(function () {
    var usersTable = $('#dt_users');
    var sharedTable = $('#dt_user_management');

    if (typeof user_management_tab === 'undefined') {
        return;
    }

    // The empty state markup is owned by /js/datatables-modern.js so every
    // listing renders the same one.
    var emptyState = (window.DataTablesModern && DataTablesModern.emptyState)
        ? DataTablesModern.emptyState
        : function () {
            return 'No records found';
        };

    var filterBar = $('#dt_filters');
    var filterSelects = filterBar.length ? filterBar.find('select[data-filter]') : $();
    var clearButton = filterBar.find('.dt-filter-clear');

    function filterValue(select) {
        return $(select).data('filter') || $(select).attr('data-filter');
    }

    // The values of the toolbar selects, keyed by the filter they belong to.
    function selectedFilters() {
        var filters = {};

        filterSelects.each(function () {
            var value = $(this).val();

            if (value) {
                filters[filterValue(this)] = value;
            }
        });

        return filters;
    }

    function updateClearButton() {
        var active = !$.isEmptyObject(selectedFilters());

        clearButton.prop('hidden', !active);

        filterSelects.each(function () {
            $(this).toggleClass('is-active', !!$(this).val());
        });
    }

    // The toolbar selects are client side select2s so the user can search their
    // values. The option list is rendered by the view, so nothing is fetched
    // from the server (no server side select2).
    var suppressReload = false;

    function applySelect2() {
        if (typeof $.fn.select2 !== 'function') {
            return;
        }

        filterSelects.each(function () {
            var select = $(this);

            if (select.hasClass('select2-hidden-accessible')) {
                return;
            }

            select.select2({
                allowClear: true,
                // The dropdown is parented to the body, never to the filter bar:
                // the bar is a flex row, so an appended dropdown would become a
                // stray flex child of the toolbar, and the bar itself clips.
                dropdownParent: $(document.body),
                minimumResultsForSearch: 0,
                placeholder: select.attr('aria-label') || 'Select…',
                theme: 'bootstrap4',
                width: 'resolve'
            });
        });
    }

    var columns = {
        users: [
            { data: 'name' },
            { data: 'username' },
            { data: 'area' },
            { data: 'roles', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false }
        ],
        roles: [
            { data: 'name' },
            { data: 'permissions', orderable: false, searchable: false },
            { data: 'users', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false }
        ],
        permissions: [
            { data: 'module' },
            { data: 'name' },
            { data: 'roles', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false }
        ]
    };

    var screenWidth = $(window).width();
    var screenHeight = $(window).height();

    var table = (user_management_tab === 'users') ? usersTable : sharedTable;
    var tabColumns = columns[user_management_tab] || [];

    // Without a column definition DataTables would initialise against the
    // markup's headers and then fail on every request, so the table is left
    // untouched instead.
    if (!table.length || !tabColumns.length) {
        return;
    }

    // The filter bar is moved into the toolbar by the layout, so the controls
    // sit on one line under the length menu and the search box. It is only
    // added when the tab actually renders one: an empty set resolves to no
    // contents and would still leave a stray, empty cell in the toolbar row.
    var layout = {
        topStart: 'pageLength',
        topEnd: 'search',
        bottomStart: 'info',
        bottomEnd: 'paging'
    };

    if (filterBar.length) {
        layout.top2Start = filterBar;
    }

    table.DataTable({
        serverSide: true,
        processing: true,
        deferRender: true,
        ajax: {
            url: '/user-management/data',
            data: function (d) {
                d.tab = user_management_tab;
                d.filters = selectedFilters();
            }
        },
        columns: tabColumns,
        layout: layout,
        language: {
            search: '<span class="sr-only">Search</span>',
            searchPlaceholder: 'Search…',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'No entries to show',
            infoFiltered: '(filtered from _MAX_ entries)',
            emptyTable: emptyState(),
            zeroRecords: emptyState(),
            processing: '<span class="dt-processing-spinner"></span> Loading…',
            loadingRecords: 'Loading…'
        },
        scrollX: (screenHeight > screenWidth) ? true : false
    });

    filterSelects.on('change', function () {
        updateClearButton();

        if (!suppressReload) {
            table.DataTable().ajax.reload();
        }
    });

    clearButton.on('click', function () {
        suppressReload = true;
        filterSelects.val(null).trigger('change.select2');
        suppressReload = false;
        updateClearButton();
        table.DataTable().ajax.reload();
    });

    applySelect2();
    updateClearButton();
});
