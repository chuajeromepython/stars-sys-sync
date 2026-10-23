$(function() {

    var screen_width = $(window).width();
    var screen_height = $(window).height();

    $('#dt_students').DataTable({
        serverSide: true,
        processing: true,
        deferRender: true,
        ajax: '/students/data',
        columns: [
            { data: 'lrn' },
            { data: 'name' },
            { data: 'gender' },
            { data: 'action', orderable: false, searchable: false }
        ],
        'scrollX': (screen_height > screen_width) ? true : false
    });
});