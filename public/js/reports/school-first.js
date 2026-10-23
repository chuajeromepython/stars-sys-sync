$(function () {
    const educationLevel = document.getElementById('education_level_id');
    const seniorHighFilters = document.getElementById('senior-high-report-filters');
    const gradeLevel = document.getElementById('grade_level_id');

    function updateSeniorHighFilters() {
        const isSeniorHigh = educationLevel.value === '3';
        const range = { '1': [1, 6], '2': [7, 10], '3': [11, 12] }[educationLevel.value];
        gradeLevel.disabled = !range;
        for (const option of gradeLevel.options) {
            if (!option.value) continue;
            const grade = Number(option.dataset.grade);
            option.disabled = !range || grade < range[0] || grade > range[1];
            option.hidden = option.disabled;
        }
        if (gradeLevel.selectedOptions[0]?.disabled) gradeLevel.value = '';
        seniorHighFilters.hidden = !isSeniorHigh;
        seniorHighFilters.querySelectorAll('select').forEach(select => {
            select.disabled = !isSeniorHigh;
        });
    }

    educationLevel.addEventListener('change', updateSeniorHighFilters);
    updateSeniorHighFilters();

    const reportTabs = $('#report-tabs [role="tab"]');
    reportTabs.on('shown.bs.tab', function () {
        reportTabs.attr('tabindex', '-1');
        $(this).attr('tabindex', '0');
    });
    reportTabs.on('keydown', function (event) {
        const index = reportTabs.index(this);
        const last = reportTabs.length - 1;
        const next = {
            ArrowRight: (index + 1) % reportTabs.length,
            ArrowLeft: (index + last) % reportTabs.length,
            Home: 0,
            End: last,
        }[event.key];
        if (next === undefined) return;
        event.preventDefault();
        reportTabs.eq(next).tab('show').trigger('focus');
    });

    $('#school_ids').select2({
        theme: 'bootstrap4',
        placeholder: 'Select one or more schools',
        width: '100%',
    });

    $('#school-report-form').on('submit', function (event) {
        event.preventDefault();

        const form = this;
        const button = $('#generate-school-reports');
        const feedback = $('#school-report-feedback');
        feedback.text('Generating your school reports...').removeClass('text-danger');
        $('#school-report-results').attr('aria-busy', 'true');
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
                feedback.text('Reports generated. Review the results beside this form.');
                toastr.success('School reports generated.');
            },
            error: function (response) {
                const message = response.responseJSON?.message
                    || 'The report could not be generated. Check your selected schools and filters.';
                toastr.error(message);
                feedback.text(message).addClass('text-danger');
            },
            complete: function () {
                button.prop('disabled', false);
                $('#school-report-results').attr('aria-busy', 'false');
                button.html('<i class="fas fa-file-alt mr-2" aria-hidden="true"></i>Generate school reports');
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
