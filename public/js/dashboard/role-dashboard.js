$(function () {
    const tableElement = $('#dashboard-records-table');

    if (tableElement.length === 0) {
        return;
    }

    const headings = {
        teachers: ['Teacher name', 'Email address'],
        students: ['Student name', 'LRN'],
        classrooms: ['Classroom name', 'Grade level'],
    };
    const table = tableElement.DataTable({
        serverSide: true,
        processing: true,
        deferRender: true,
        ajax: {
            url: tableElement.data('records-url'),
            data: function (data) {
                data.type = $('#dashboard-record-type').val();
                data.academic_year_id = $('#dashboard-academic-year').val();
            },
        },
        columns: [
            { data: 'record' },
            { data: 'detail' },
            { data: 'action', orderable: false, searchable: false },
        ],
        order: [[0, 'asc']],
        language: {
            emptyTable: 'No records are available in your assigned scope.',
        },
        initComplete: function () {
            updateDashboardRecordHeadings(headings);
        },
    });

    $('#dashboard-record-type').on('change', function () {
        updateDashboardRecordHeadings(headings);
        table.ajax.reload();
    });

    $('#dashboard-academic-year').on('change', function () {
        table.ajax.reload();
    });
});

function updateDashboardRecordHeadings(headings) {
    const labels = headings[$('#dashboard-record-type').val()];

    $('#dashboard-records-table thead th').eq(0).text(labels[0]);
    $('#dashboard-records-table thead th').eq(1).text(labels[1]);
    $('#dashboard-records-table thead th').eq(2).text('Actions');
}
