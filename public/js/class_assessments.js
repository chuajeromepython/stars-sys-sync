$(function() {
	
	$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

	var screen_width = $(window).width();
    var screen_height = $(window).height();
    var card_answer_key_height = $('#card_answer_key').height();
    $('#table_student').css("min-height", card_answer_key_height);

    $('#dt_students').dataTable({
	    'language':{
	        'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
	    },
	    'scrollX': (screen_height > screen_width) ? true : false
	});

	$('#dt_students tbody').on( 'click', '.btn-students', function () {
        
        var student_id = $(this).data('id');
        var label = '<i class="fa fa-user mr-2"></i>'+$(this).data('name');

        $('#label_student').html(label);
        $('#student_id').val(student_id);
        getStudentAnswers(class_assessment_id, student_id);
        window.scrollTo(0, 0);

    });


});

function getStudentAnswers(class_assessment_id, student_id){
	$.ajax({
        url: '/getStudentAnswers',
        type: "POST",
        data: {
            "class_assessment_id" : class_assessment_id,
            "student_id" : student_id
        },
        success: function(data){
        	var count = 1;
        	var html = ""
			$.each(data, function(i, row) {

				if (row.is_correct == 1) {
					icon = '<i class="mr-2 fa fa-check text-success"></i>';
				}else{
					icon = '<i class="mr-2 fa fa-times text-danger"></i>';
				}
				var a  = (row.answer == "A") ? 'selected' : '';
				var b = (row.answer == "B") ? 'selected' : '';
				var c  = (row.answer == "C") ? 'selected' : '';
				var d = (row.answer == "D") ? 'selected' : '';

				if (count-1 % 5 == 0) {html +="<tr>";}
					html += '<td>\
	                    <center>\
	                    	<input type="hidden" name="student_answer_id[]" value="'+row.id+'">	\
	                    	<input type="hidden" name="item_number[]" value="'+row.item_number+'">	\
	                        <span>'+icon+" "+row.item_number+'</span>\
		                    <select class="ml-2 my-select" name="answer[]">\
		                        <option value="A" '+a+'>A</option>\
		                        <option value="B" '+b+'>B</option>\
		                        <option value="C" '+c+'>C</option>\
		                        <option value="D" '+d+'>D</option>\
		                    </select>\
	                    </center>\
	                </td>';
                if (count % 5 == 0) {html +="</tr>";}

				count++;
			});     

			$('#table_student').html(html);
			toastr.info("Student answer successfully loaded!");
			$('#div_answer_key').removeClass('col-md-12');
			$('#div_answer_key').addClass('col-md-6');
			$('#div_student').fadeIn();
		},
	});
}