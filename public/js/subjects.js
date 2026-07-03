$(function() {

    var screen_width = $(window).width();
    var screen_height = $(window).height();
    
 	$('#dt_subjects').dataTable({
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
    });
 	$('#dt_subjects tbody').on( 'click', '.btn-edit', function () {
       
        var id = $(this).data('edit_id');
        var name = $(this).data('edit_name');
        $('#edit_name').val(name);
        $('#edit_id').val(id);

    });
    $('#dt_subjects tbody').on( 'click', '.btn-destroy', function () {
       
        var id = $(this).data('destroy_id');
        var name = $(this).data('destroy_name');
        $('#destroy_name').html(name);
        $('#destroy_id').val(id);
        
    });
});