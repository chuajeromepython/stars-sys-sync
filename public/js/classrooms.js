$(function() {
	
	var screen_width = $(window).width();
    var screen_height = $(window).height();
	
	$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });



	var count = 1;
	$('.btn-edit').click(function(){
		$('.overlay').show();
		var classroom_id = $(this).data('classroom_id');
		var section = $(this).data('section');
		var advisor = $(this).data('advisor');
		$('#edit_classroom_id').val(classroom_id);
		getSectionsPerSchool(section);
		getTeachersPerSchool(advisor);
		setTimeout(() => {
        	$('.overlay').fadeOut('slow')
      	}, 1000);
	});

	$('.btn-destroy').click(function(){
		var classroom_id = $(this).data('classroom_id');
		var section = $(this).data('section');
		var grade_level = $(this).data('grade_level');
		$('#destroy_name').html(grade_level+'-'+section);
		$('#destroy_classroom_id').val(classroom_id);
	});
	$('.div-room').click(function(){
		var id = $(this).data('id');
		window.location = "/classrooms/"+id;
	});
	$('#btn_add_subject_teacher').click(function(){
	    var append_subject_teacher = appendSubjectTeacher(count);
	    $('.div-subject').append(append_subject_teacher);
	    $('.select2bs4').select2({
	      	theme: 'bootstrap4'
	    });
	    count++;

	   
	});

	$(document).on('change', '#grade_levels',function(){
	   	level = parseInt($(this).val());
	   	if(level > 10 && level < 13){
	   		$('.div-shs').fadeIn();
	   	}else{
	   		$('.div-shs').fadeOut();
	   	}
	});

	$(document).on('change', '#tracks',function(){
		track_id = $(this).val();
	   	getStrands(track_id);
	});
	$(document).on('change', '#strands',function(){
		strand_id = $(this).val();
	   	getCourses(strand_id);
	});

	$(document).on('click', '.btn-remove',function(){
	   $(this).closest('div.row').remove();
	});

	$(document).on('change', '.select-subject',function(){
	   	val = parseInt($(this).val());
	    id = $(this).attr('id');
	   	selected = getSelectedOptions(id);
	   	existing = jQuery.inArray(val, selected);
	   	if(existing >= 0){
	   		toastr.error("This subject already existed in this class.");
	   		$(this).closest('div.row').remove();
	   	}
	});



	// Show
	$('#dt_classes').dataTable({
        'order': [[3, 'desc']],
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
    });
     $('#dt_students').dataTable({
        'order': [[1, 'asc']],
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'scrollX': (screen_height > screen_width) ? true : false
    });

    $(document).on('click', '#btn_add', function(){
	   	getTeachers();
	});

});



function appendSubjectTeacher(count){

	var t_opt = "";
	var s_opt = "";
   
	$.each(teachers, function(i, teacher) {
        t_opt += '<option value="'+teacher.id+'">'+teacher.first_name+' '+teacher.last_name+'</option>'
    });
    $.each(subjects, function(i, subject) {
        s_opt += '<option value="'+subject.id+'">'+subject.title+'</option>'
    });

    html = '<div class="row mt-1"><div class="mb-1 col-md-6">\
	        <label class="text-muted">Class Adviser*</label>\
	        <select class="select2bs4 form-control" name="teachers[]">\
	            <option selected>-Select Subject Teacher-</option>\
	            '+t_opt+'\
	        </select>\
	    </div>\
	    <div class="col-md-5">\
	        <label class="text-muted">Subject*</label>\
	        <select class="select2bs4 form-control select-subject" id="s'+count+'" name="subjects[]" >\
	        	<option selected>-Select Subject-</option>\
	        	'+s_opt+'\
	        </select>\
	    </div>\
	    <div class="col-md-1">\
	    	<br>\
	    	<a href="#" class="btn btn-block btn-danger btn-remove mt-2"><i class="fa fa-times"></i></a>\
	    </div>\
    </div>';

    return html;
  
}

function getSelectedOptions(old_id){
	var selected = [];
	$('.select-subject').each(function(){
		value = parseInt($(this).val());
		id = $(this).attr('id');
		if(value != null){
			if(old_id != id){
				selected.push(value);
			}	
		}
	});

	return selected;
}


function getStrands(track_id){
	$.ajax({
        url: '/getStrands',
        type: "POST",
        data: {
            "track_id" : track_id
        },
        success: function(data){
            
            var options = '<option selected="" value="" > -Select Strands- </option>';
            $.each(data, function(i, item) {
               options += '<option value="'+data[i].id+'">'+data[i].name+'</option>';
            });
            $('#strands').html(options);    
        },
    });
}


function getCourses(strand_id){
	$.ajax({
        url: '/getCourses',
        type: "POST",
        data: {
            "strand_id" : strand_id
        },
        success: function(data){
            
            var options = '<option selected="" value="" > -Select Courses- </option>';
            $.each(data, function(i, item) {
               options += '<option value="'+data[i].id+'">'+data[i].course+'</option>';
            });
            $('#courses').html(options);    
        },
    });
}

function getTeachers(){
	$.ajax({
        url: '/getTeachers',
        type: "POST",
        success: function(data){
            var options = '<option selected="" value="" > -Select Teachers- </option>';
            $.each(data, function(i, item) {
               options += '<option value="'+data[i].id+'">'+data[i].first_name+' '+data[i].middle_name+' '+data[i].last_name+'</option>';
            });
            $('#teachers').html(options);
            toastr.info("Teachers successfully loaded")
        },
    });
}

function getTeachersPerSchool(advisor){
	$.ajax({
        url: '/getTeachersPerSchool',
        type: "POST", data: {
            "school_id" : school_id
        },
        success: function(data){
            
            var options = '<option selected="" value="" > -Select Teachers- </option>';
            $.each(data, function(i, item) {
            	var name = data[i].first_name+' '+data[i].last_name;
           		if (advisor == name) {
           			options += '<option value="'+data[i].id+'" selected>'+name+'</option>';
           		}else{
               		options += '<option value="'+data[i].id+'">'+name+'</option>';
           		}
            });
            $('#edit_teacher').html(options);
            // toastr.info("Teachers successfully loaded")
        },
    });
}
function getSectionsPerSchool(section){
	$.ajax({
        url: '/getSectionsPerSchool',
        type: "POST", data: {
            "school_id" : school_id
        },
        success: function(data){
            
            var options = '<option selected="" value="" > -Select Sections- </option>';
            $.each(data, function(i, item) {
            	var name = data[i].section;
           		if (section == name) {
           			options += '<option value="'+data[i].id+'" selected>'+name+'</option>';
           		}else{
               		options += '<option value="'+data[i].id+'">'+name+'</option>';
           		}
            });
            $('#edit_section').html(options);
            // toastr.info("Sections successfully loaded")
        },
    });
}


