$(function() {


    var screen_width = $(window).width();
    var screen_height = $(window).height();
    
 	$('#dt_districts').dataTable({
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
    });

 	$('#dt_districts tbody').on( 'click', '.btn-edit', function () {
       
        var id = $(this).data('edit_id');
        var name = $(this).data('edit_name');
        $('#edit_name').val(name);
        $('#edit_id').val(id);

    });

    $('#dt_districts tbody').on( 'click', '.btn-destroy', function () {
       
        var id = $(this).data('destroy_id');
        var name = $(this).data('destroy_name');
        $('#destroy_name').html(name);
        $('#destroy_id').val(id);
        
    });

    for (var i = 0; i < 5; i++) {
        console.log(Math.floor(Math.random() * 30) + 16);
    }

});