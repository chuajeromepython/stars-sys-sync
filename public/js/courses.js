$(function() {

    var screen_width = $(window).width();
    var screen_height = $(window).height();

 	$('#dt_courses').dataTable({
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
    });
    
 	$('#dt_courses tbody').on( 'click', '.btn-edit', function () {
       
        var id = $(this).data('edit_id');
        var name = $(this).data('edit_name');
        var strand_id = $(this).data('edit_strand_id');
        var strand_options = "";
        
        $.each(strands, function(i, strand) {
            if(strand_id == strand.id){
                strand_options += '<option value="'+strand.id+'" selected>'+strand.name+'</option>';
            }else{
                strand_options += '<option value="'+strand.id+'">'+strand.name+'</option>';
            }
            
        });

        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_strand').html(strand_options);

    });
    
    $('#dt_courses tbody').on( 'click', '.btn-destroy', function () {
       
        var id = $(this).data('destroy_id');
        var name = $(this).data('destroy_name');
        $('#destroy_name').html(name);
        $('#destroy_id').val(id);
        
    });
});