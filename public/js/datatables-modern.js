/*
 * Shared DataTables defaults for the modern table skin
 * (/css/datatables-modern.css).
 *
 * Loaded straight after the DataTables build and before any page script, so
 * every listing - server side or client side - picks up the same control
 * wording, loading indicator and empty state instead of repeating them table by
 * table. A table that passes its own `language` options still wins, so nothing
 * here overrides a deliberate per table choice.
 */
(function ($) {
    'use strict';

    var emptyStateMarkup = '<span class="dt-empty-state">'
        + '<i class="fa fa-inbox"></i>'
        + '<span>No records found</span>'
        + '</span>';

    var processingMarkup = '<span class="dt-processing-spinner"></span> Loading…';

    /*
     * Exposed so the module scripts render exactly the same state as the
     * defaults applied below.
     */
    window.DataTablesModern = {
        emptyState: function () {
            return emptyStateMarkup;
        },
        processing: function () {
            return processingMarkup;
        }
    };

    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            search: '<span class="sr-only">Search</span>',
            searchPlaceholder: 'Search…',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'No entries to show',
            infoFiltered: '(filtered from _MAX_ entries)',
            emptyTable: emptyStateMarkup,
            zeroRecords: emptyStateMarkup,
            processing: processingMarkup,
            loadingRecords: 'Loading…'
        }
    });
})(jQuery);
