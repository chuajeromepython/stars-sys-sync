$(function() {
    

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Users => Index
    var classification =  $('#classification').val();
    getArea(classification);

    $('#classification').on('change', function() {
        var classification =  $(this).val();
        getArea(classification);
        
    });

    $('#upload_classification').on('change', function() {
        var classification =  $(this).val();
        if(classification == "School Head"){
            var link = '/school_supervisors/upload';
        }
        
        $('#upload_form').attr('action', link);
        
    });


    $('#dt_users tbody').on( 'click', '.btn-reset', function () {
        
        var username = $(this).data('reset_username');
        var id = $(this).data('reset_id');

        $('#reset_username').html(username);
        $('#reset_id').val(id);
    });

    $('#dt_users tbody').on( 'click', '.btn-destroy', function () {
        
        var username = $(this).data('destroy_username');
        var id = $(this).data('destroy_id');

        $('#destroy_username').html(username+"'s");
        $('#destroy_id').val(id);
    });

});




function getArea(classification){
    $.ajax({
        url: '/getArea',
        type: "POST",
        data: {
            "classification" : classification
        },
        success: function(data){
            var request_area = $("#request_area").val();
            var optons = '<option selected="" value="" > -Select Area- </option>';
            $.each(data, function(i, item) {
                if(request_area == data[i].name){
                    optons += '<option value="'+data[i].name+'" selected>'+data[i].name+'</option>';
                }else{
                    optons += '<option value="'+data[i].name+'">'+data[i].name+'</option>';
                }
                
            });
            $('#select_area').html(optons);    
            
        }, //end of success getMunicipalitiesByDistrict
    });
}

