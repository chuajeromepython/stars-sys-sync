$(function() {

    var screen_width = $(window).width();
    var screen_height = $(window).height();

    $('#dt_department_heads').dataTable({
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
    });

    $('#dt_department_heads tbody').on( 'click', '.btn-destroy', function () {
        
        var username = $(this).data('destroy_username');
        var id = $(this).data('destroy_id');

        $('#destroy_username').html(username+"'s");
        $('#destroy_id').val(id);
        
    });

});