 $(function() {
    
    var screen_width = $(window).width();
    var screen_height = $(window).height();
    $('.btn-cancel').click(function() {
        location.reload();
    });   
    $('#dt_students').dataTable({
        'order': [[1, 'asc']],
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
    });

    $('#dt_classes').dataTable({
        'order': [[1, 'asc']],
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.btn-search').click(function(){
        var lrn = $('#lrn').val();
        searchLRN(lrn);
    });

    $('#dt_students tbody').on( 'click', '.btn-edit', function () {
        
        var name = $(this).data('name');
        var student_id = $(this).data('student_id');

        $('#edit_name').html(name);
        $('#edit_student_id').val(student_id);
        // $('#destroy_teacher_class_id').val(student_id);
    });

    $('#dt_classes tbody').on( 'click', '.btn-destroy', function () {
        var id = $(this).data('id');
        var name = $(this).data('subject');
        $('#destroy_name').html(name);
        $('#destroy_teacher_class_id').val(id);
    });

});


function searchLRN(lrn){
    $.ajax({
        url: '/searchLRN',
        type: "POST",
        data: {
            "lrn" : lrn
        },
        success: function(data){

            has_error = 'error' in data;

            if(has_error === false){
                toastr.info(data.message);
                html = '<input type="hidden" value="'+data.student.id+'" name="student_id">\
                        <table class="table table-bordered mt-3">\
                        <tr>\
                            <th>LRN</th>\
                            <td>'+data.student.lrn+'</td>\
                        </tr>\
                        <tr>\
                            <th>First Name</th>\
                            <td>'+data.student.first_name+'</td>\
                        </tr>\
                        <tr>\
                            <th>Middle Name</th>\
                            <td>'+data.student.middle_name+'</td>\
                        </tr>\
                        <tr>\
                            <th>Last Name</th>\
                            <td>'+data.student.last_name+'</td>\
                        </tr>\
                        <tr>\
                            <th>Suffix</th>\
                            <td>'+data.student.suffix+'</td>\
                        </tr>\
                        <tr>\
                            <th>Sex</th>\
                            <td>'+data.student.gender+'</td>\
                        </tr>\
                    </table>';
                $('.div-students').html(html);
            }else{
                toastr.error(data.error);
                $('.div-students').html("");
            }
        },
    });
}