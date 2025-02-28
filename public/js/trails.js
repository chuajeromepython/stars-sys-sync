$(function() {
    

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#models').on('change', function() {
        model = $('#models').val();
        getTrails(model);
    });

    // $('#btn_generate').on( 'click', function () {
        
    //     model = $('#models').val();
    //     alert(model);
    //     getTrails(model);
        
    // });



});

function getTrails(model){
    $(".table").dataTable().fnDestroy()
    $.ajax({
        url: '/trails/'+model,
        type: "GET",
        success: function(data){
            html = '';
            $.each(data, function(i, audit) {
                html+='<tr>\
                    <td>'+audit.created_at+'</td>\
                    <td>'+audit.event+'</td>\
                    <td>'+audit.username+'</td>\
                    <td>'+audit.auditable_id+'</td>\
                    <td style="width: 30%">\
                        <small><ul>';
                        $.each($.parseJSON(audit.old_values), function(o, old) {
                            html+='<li><b>'+o+'</b>: '+old+'</li>'
                        })
                        html+='</ul>\
                    </td>\
                    <td style="width: 30%">\
                        <small><ul>';
                        $.each($.parseJSON(audit.new_values), function(n, news) {
                            html+='<li><b>'+n+'</b>: '+news+'</li>'
                        })
                        html+='</ul>\
                    </td>\
                </tr>';
            });

            $('#tbody').html(html);
            $('.table').dataTable({
                pageLength: 5
            });
            $('.table').show();
        }, 
    });
}