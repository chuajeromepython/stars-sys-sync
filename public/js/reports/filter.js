var screen_width = $(window).width();
var screen_height = $(window).height();

$(function() {

	$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

	$(document).on('change', '#education_level',function(){
        
	   	education_level_id = parseInt($(this).val());
	   	getGradeLevelPerEducationLevel(education_level_id);
	   	visibility = (education_level_id == 3) ? "show" : "hide";
	   	displaySHSFilter(visibility);
        $('#district').val(0).change();

	});

	$(document).on('change', '#grade_level',function(){

	   	grade_level_id = parseInt($(this).val());
	   	getAssessmentTypePerGradeLevel(grade_level_id);
	   	getSubjectClassPerGradeLevel(grade_level_id);
	   	visibility = (grade_level_id == 11 || grade_level_id == 12) ? "show" : "hide";
        (grade_level_id == 13) ? $('#div_subject').fadeOut() : $('#div_subject').fadeIn();
	   	displaySHSFilter(visibility);
        $('#district').val(0).change();

	});

	$('#btn_show_district').click(function(){
		$('.div_district').fadeIn();
        getDistricts();

	});
    $('#btn_show_school').click(function(){
        district_id = $('#district').val();
        grade_level_id = $('#grade_level').val();
        getSchools(district_id, grade_level_id);
        $('.div_school').fadeIn();
    });
    $('#btn_show_teacher').click(function(){
        school_id = $('#school').val();
        getTeachers(school_id);
        $('.div_teacher').fadeIn();
    });

    $('#btn_close_district').click(function(){
        $('.div_district').fadeOut();
        $('#district').val(0).change();
    });
     $('#btn_close_school').click(function(){
        $('.div_school').fadeOut();
        $('#school').val(0).change();
    });
    $('#btn_close_teacher').click(function(){
        $('.div_teacher').fadeOut();
        $('#teacher').val(0).change();
    });

    $(document).on('change', '#track',function(){
        track_id = $(this).val();
        getStrands(track_id);
    });
    $(document).on('change', '#district',function(){
        district_id = $(this).val();
        grade_level_id = $('#grade_level').val();
        getSchools(district_id, grade_level_id);
    });
    $(document).on('change', '#school',function(){
        school_id = $(this).val();
        getTeachers(school_id);
    });
})


/***************************
	FUNCTIONS STARTS HERE
****************************/


function getGradeLevelPerEducationLevel(education_level_id){
	$.ajax({
        url: '/getGradeLevelPerEducationLevel',
        type: "POST",
        data: {
            "education_level_id" : education_level_id
        },
        success: function(data){
            
            var options = '<option selected="" value="" > -Select Grade Level- </option>';
            $.each(data, function(i, item) {
               options += '<option value="'+data[i].id+'">'+data[i].level+'</option>';
            });
            $('#grade_level').html(options);    
            $('#grade_level').select2({	theme: 'bootstrap4' });
        },
    });
}

function getAssessmentTypePerGradeLevel(grade_level_id){
	$.ajax({
        url: '/getAssessmentTypePerGradeLevel',
        type: "POST",
        data: {
            "grade_level_id" : grade_level_id
        },
        success: function(data){
            
            var options = '<option selected="" value="" > -Select Assessment Type- </option>';
            $.each(data, function(i, item) {
               options += '<option value="'+data[i].id+'">'+data[i].type+'</option>';
            });
            $('#assessment_type').html(options);    
            $('#assessment_type').select2({	theme: 'bootstrap4' });

        },
    });
}

function getSubjectClassPerGradeLevel(grade_level_id){
	$.ajax({
        url: '/getSubjectClassPerGradeLevel',
        type: "POST",
        data: {
            "grade_level_id" : grade_level_id
        },
        success: function(data){
            
            var options = '<option selected="" value="" > -Select Subject- </option>';
            $.each(data, function(i, item) {
               options += '<option value="'+data[i].id+'">'+data[i].title+'</option>';
            });
            $('#subject').html(options);    
            $('#subject').select2({	theme: 'bootstrap4' });

        },
    });
}

function displaySHSFilter(visibility){
	if(visibility == "show"){
   		$('#div_track').fadeIn();
   		$('#div_strand').fadeIn();
   		$('#div_semester').fadeIn();
   	}else{
   		$('#div_track').fadeOut();
   		$('#div_strand').fadeOut();
   		$('#div_semester').fadeOut();
   	}
}

function getStrands(track_id){
    $.ajax({
        url: '/getStrands',
        type: "POST",
        data: {
            "track_id" : track_id
        },
        success: function(data){
            
            var optons = '<option selected="" value="" > -Select Strands- </option>';
            $.each(data, function(i, item) {
               optons += '<option value="'+data[i].id+'">'+data[i].name+'</option>';
            });
            $('#strand').html(optons);    
        },
    });
}

function getDistricts(){
    $.ajax({
        url: '/getDistricts',
        type: "POST",
        success: function(data){
            
            var optons = '<option selected="" value="0" > -Select District- </option>';
            $.each(data, function(i, item) {
               optons += '<option value="'+data[i].id+'">'+data[i].name+'</option>';
            });
            $('#district').html(optons);    
        },
    });
}

function getSchools(district_id, grade_level_id){
    $.ajax({
        url: '/getSchools',
        type: "POST",
        data: {
            "district_id" : district_id,
            "grade_level_id" : grade_level_id
        },
        success: function(data){
            
            var optons = '<option selected="" value="" > -Select School- </option>';
            $.each(data, function(i, item) {
               optons += '<option value="'+data[i].id+'">'+data[i].name+'</option>';
            });
            $('#school').html(optons);    
        },
    });
}

function getTeachers(school_id){
    $.ajax({
        url: '/getTeachers',
        type: "POST",
        data: {
            "school_id" : school_id
        },
        success: function(data){
            
            var optons = '<option selected="" value="" > -Select Teacher- </option>';
            $.each(data, function(i, item) {
               optons += '<option value="'+data[i].id+'">'+
                data[i].first_name+' '+data[i].middle_name+' '+data[i].last_name
                +'</option>';
            });

            $('#teacher').html(optons);    
        },
    });
}


