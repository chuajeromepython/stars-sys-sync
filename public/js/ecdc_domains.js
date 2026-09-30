/**
 * ECDC domain and competency management.
 *
 * The listing tables are rendered server side and enhanced with DataTables
 * here, matching the other management modules (districts, grade levels).
 */
$(function() {

    var screen_width = $(window).width();
    var screen_height = $(window).height();

    var domain_table = $('#dt_ecdc_domains');

    if (domain_table.length) {
        domain_table.dataTable({
            'scrollX': (screen_height > screen_width) ? true : false
        });

        domain_table.find('tbody').on('click', '.btn-edit', function () {

            $('#edit_id').val($(this).data('edit_id'));
            $('#edit_domain').val($(this).data('edit_domain'));

        });

        domain_table.find('tbody').on('click', '.btn-destroy', function () {

            $('#destroy_id').val($(this).data('destroy_id'));
            $('#destroy_domain').html($(this).data('destroy_domain'));

        });
    }

    var competency_table = $('#dt_ecdc_competencies');

    if (competency_table.length) {
        competency_table.dataTable({
            'scrollX': (screen_height > screen_width) ? true : false
        });

        competency_table.find('tbody').on('click', '.btn-edit', function () {

            $('#edit_competency_id').val($(this).data('edit_id'));
            $('#edit_competency').val($(this).data('edit_competency'));

        });

        competency_table.find('tbody').on('click', '.btn-destroy', function () {

            $('#destroy_competency_id').val($(this).data('destroy_id'));
            $('#destroy_competency').html($(this).data('destroy_competency'));

        });
    }

});
