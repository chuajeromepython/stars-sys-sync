$(function () {
    $('#school_ids').select2({
        theme: 'bootstrap4',
        placeholder: 'Select one or more schools',
        width: '100%',
    });

    $('#school-report-form').on('submit', function (event) {
        event.preventDefault();

        const form = this;
        const button = $('#generate-school-reports');
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i>Generating');

        $.ajax({
            url: form.action,
            type: 'POST',
            data: $(form).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                renderSchoolReports(response.reports || []);
                toastr.success('School reports generated.');
            },
            error: function (response) {
                const message = response.responseJSON?.message
                    || 'The report could not be generated. Check your selected schools and filters.';
                toastr.error(message);
            },
            complete: function () {
                button.prop('disabled', false);
                button.html('<i class="fas fa-file-alt mr-2" aria-hidden="true"></i>Generate reports');
            },
        });
    });
});

function renderSchoolReports(reports) {
    const results = document.getElementById('school-report-results');
    results.replaceChildren();

    if (reports.length === 0) {
        const emptyState = document.createElement('div');
        emptyState.className = 'alert alert-info';
        emptyState.textContent = 'No report data was returned for the selected schools.';
        results.append(emptyState);
        return;
    }

    reports.forEach((schoolReport) => {
        const card = document.createElement('section');
        card.className = 'card';

        const header = document.createElement('div');
        header.className = 'card-header';

        const title = document.createElement('h2');
        title.className = 'card-title text-primary font-weight-bold';
        title.textContent = `${schoolReport.school_name} - ${reportTypeName(schoolReport.report_type_id)}`;
        header.append(title);
        card.append(header);

        const body = document.createElement('div');
        body.className = 'card-body table-responsive';
        appendReportTable(body, schoolReport.report);
        card.append(body);
        results.append(card);
    });
}

function reportTypeName(type) {
    return {
        LC: 'Level of Competencies',
        AL: 'Achievement Level',
        SA: 'Score Analysis',
    }[type] || 'Report';
}

function appendReportTable(container, report) {
    const rows = report && typeof report === 'object' && report.data
        ? report.data
        : report;

    if (!rows || typeof rows !== 'object' || Object.keys(rows).length === 0) {
        const emptyState = document.createElement('p');
        emptyState.className = 'text-muted mb-0';
        emptyState.textContent = 'No records match the selected filters for this school.';
        container.append(emptyState);
        return;
    }

    const entries = Object.entries(rows);
    const columns = [...new Set(entries.flatMap(([, row]) => (
        row && typeof row === 'object' ? Object.keys(row) : []
    )))];
    const table = document.createElement('table');
    table.className = 'table table-bordered table-striped';
    const head = document.createElement('thead');
    const headRow = document.createElement('tr');

    ['Category', ...columns].forEach((column) => {
        const cell = document.createElement('th');
        cell.scope = 'col';
        cell.textContent = column;
        headRow.append(cell);
    });

    head.append(headRow);
    table.append(head);

    const body = document.createElement('tbody');
    entries.forEach(([label, row]) => {
        const tableRow = document.createElement('tr');
        const labelCell = document.createElement('th');
        labelCell.scope = 'row';
        labelCell.textContent = label;
        tableRow.append(labelCell);

        columns.forEach((column) => {
            const valueCell = document.createElement('td');
            valueCell.textContent = formatReportValue(row?.[column]);
            tableRow.append(valueCell);
        });

        body.append(tableRow);
    });

    table.append(body);
    container.append(table);
}

function formatReportValue(value) {
    if (value === null || value === undefined) {
        return '';
    }

    if (typeof value !== 'object') {
        return String(value);
    }

    return Object.entries(value)
        .map(([key, item]) => `${key}: ${formatReportValue(item)}`)
        .join(' / ');
}
