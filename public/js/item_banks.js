
var screen_width = $(window).width();
var screen_height = $(window).height();

$(function() {

    
    $('#dt_items').dataTable({
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

    $(document).on('change', '#academic_year_id', function(){
        var academic_year_id = $(this).val();

        getGradeLevelPerAcademicYear(academic_year_id);    

    });
    $(document).on('change', '#grade_level_id', function(){
        var parameters = [];
        parameters['academic_year_id'] = $('#academic_year_id').val();
        parameters['grade_level_id'] = $('#grade_level_id').val();
      
        getSubjectPerGradeLevel(parameters);    

    });


    $('#btn_generate').click(function(){

        var parameters = [];

        if (
            $('#academic_year_id').val() != 0 &&
            $('#grade_level_id').val() != 0 &&
            $('#subject_id').val() != 0
        ){
            parameters['academic_year_id'] = $('#academic_year_id').val();    
            parameters['grade_level_id'] = $('#grade_level_id').val(); 
            parameters['subject_id'] = $('#subject_id').val();
            getItems(parameters);
        }else{
            ( $('#academic_year_id').val() == 0 ) ? toastr.error("Please Select Academic Year") : "";
            ( $('#grade_level_id').val() == 0 ) ? toastr.error("Please Select Grade Level") : "";  
            ( $('#subject_id').val() == 0 ) ? toastr.error("Please Select Subject") : ""; 

            $('#dt_items').dataTable().fnClearTable();   
        }
        

          
    });

});

function getGradeLevelPerAcademicYear(academic_year_id){
    $.ajax({
        url: '/getGradeLevelPerAcademicYear',
        type: "POST",
        data: {
            "academic_year_id" : academic_year_id
        },
        success: function(data){
            var options = '<option selected="" value="0" > -Select Grade Level- </option>';
            $.each(data, function(i, item) {
                options += '<option value="'+data[i].id+'">'+data[i].level+'</option>';
            });

            $('#grade_level_id').html(options);    
        },
    });
}

function getSubjectPerGradeLevel(parameters){
    $.ajax({
        url: '/getSubjectPerGradeLevel',
        type: "POST",
        data: {
            "academic_year_id" : parameters.academic_year_id,
            "grade_level_id" : parameters.grade_level_id,
        },
        success: function(data){
            var options = '<option selected="" value="0" > -Select Subject- </option>';
            $.each(data, function(i, item) {
                options += '<option value="'+data[i].id+'">'+data[i].title+'</option>';
            });
            $('#subject_id').html(options);    
        },
    });
}


function getItems(parameters){

    $('#dt_items').dataTable().fnDestroy();
    $.ajax({
        url: '/getItems',
        type: "POST",
        data: {
            "academic_year_id" : parameters.academic_year_id,
            "grade_level_id" : parameters.grade_level_id,
            "subject_id" : parameters.subject_id,
        },
        success: function(data){

            var html = "";
            $.each(data, function(i, items) {
                var item_options = items.options;
                var html_options = ""
                $.each(item_options, function(key, option) {
                    color = (option.is_correct == 0) ? "bg-dark" : "bg-success"
                    html_options += '<span class="badge '+color+' badge-pill mr-2">'+option.assignment+' . </span>';
                    html_options += option.option+"<br>";
                    console.log(item_options[key])
                });    

                html +=  "<tr>\
                    <td>"+items.code+"</td>\
                    <td>"+items.question+"</td>\
                    <td>"+html_options+"</td>\
                </tr>";
            }); 
            $('#item_body').html(html);
            $('#dt_items').dataTable({
                'language':{
                    'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
                },
                'pageLength': 5,
                'scrollX': (screen_height > screen_width) ? true : false
            });

        },
    });
}