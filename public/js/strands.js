$(function() {

    var screen_width = $(window).width();
    var screen_height = $(window).height();

 	$('#dt_strands').dataTable({
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
    });
    
 	$('#dt_strands tbody').on( 'click', '.btn-edit', function () {
       
        var id = $(this).data('edit_id');
        var name = $(this).data('edit_name');
        var track_id = $(this).data('edit_track_id');
        var track_options = "";
        
        $.each(tracks, function(i, track) {
            if(track_id == track.id){
                track_options += '<option value="'+track.id+'" selected>'+track.name+'</option>';
            }else{
                track_options += '<option value="'+track.id+'">'+track.name+'</option>';
            }
            
        });

        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_track').html(track_options);

    });
    
    $('#dt_strands tbody').on( 'click', '.btn-destroy', function () {
       
        var id = $(this).data('destroy_id');
        var name = $(this).data('destroy_name');
        $('#destroy_name').html(name);
        $('#destroy_id').val(id);
        
    });
});