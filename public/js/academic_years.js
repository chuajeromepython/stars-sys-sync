$(function() {

    var screen_width = $(window).width();
    var screen_height = $(window).height();
    $('#dt_academic_years').dataTable({
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
        
    });

    $('#dt_academic_years tbody').on( 'click', '.btn-edit', function () {
        var from = $(this).data('from');
        var to = $(this).data('to');
        var status = $(this).data('status');
        var id = $(this).data('id');
        
        var options = '';
        if(status == 1){
            options+='<option value="1" selected>ACTIVE</option>';
            options+='<option value="0">INACTIVE</option>';
        }else{
            options+='<option value="1">ACTIVE</option>';
            options+='<option value="0" selected>INACTIVE</option>';
        }

        $('#edit_is_active').html(options);
        $('#edit_from').val(from);
        $('#edit_to').val(to);
        $('#edit_id').val(id);
    });

    $('#dt_academic_years tbody').on( 'click', '.btn-destroy', function () {

        var from = $(this).data('from');
        var to = $(this).data('to');
        var status = $(this).data('status');
        var id = $(this).data('id');

        var label = from+"-"+to;
        $('#destroy_id').val(id);
        $('#destroy_label').html(label);

    });
});