$(function() {

     var screen_width = $(window).width();
    var screen_height = $(window).height();

    $('#dt_teachers').DataTable({
        serverSide: true,
        processing: true,
        deferRender: true,
        ajax: '/teachers/data',
        columns: [
            { data: 'username' },
            { data: 'name' },
            { data: 'action', orderable: false, searchable: false }
        ],
        'scrollX': (screen_height > screen_width) ? true : false
    });

	$('#dt_teachers tbody').on( 'click', '.btn-destroy', function () {
        
        var username = $(this).data('destroy_username');
        var id = $(this).data('destroy_id');

        $('#destroy_username').html(username+"'s");
        $('#destroy_id').val(id);
        
    });

    

});