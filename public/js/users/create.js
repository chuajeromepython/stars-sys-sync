$(function() {
    
    $('').css({
        'background-color' : 'blue'
    })
   
    // Users => create
    $('#create_classification').on('change', function() {
        
        var classification =  $(this).val();
        generateOptions(classification);
        
    });


});

function generateOptions(classification){

    $('#div_division').hide();
    $('#div_district').hide();
    $('#div_school').hide();
    $('#div_subject').hide();
   
    if(
        classification == "Division Supervisor" || 
        classification == "Division Administrator" || 
        classification == "Division Superintendent" || 
        classification == "Assistant Division Superintendent" || 
        classification == "Chief of CID" || 
        classification == "Chief of SGOD" || 
        classification == "Division Supervisor"  

    ){  

        var division_option = '';
        $.each(options.divisions, function(i, division) {
            division_option += '<option value="'+division.id+'">'+division.name+'</option>'
        });

        $('#division').html(division_option);
        $('#div_division').show();

        if(classification == "Division Supervisor"){
            var subject_option = '<option></option>';
            $.each(options.subjects, function(i, subject) {
                subject_option += '<option value="'+subject.id+'">'+subject.title+'</option>'
            });

            $('#subject').html(subject_option);
            $('#div_subject').show();
            $('#subject').select2({
                multiple: true,
            });
        }else{
            $('#div_subject').hide();
        }
        
    }


    if(
        classification == "School Head" ||
        classification == "Department Head" ||
        classification == "Teacher" 
    ){
        var school_option = '';
        $.each(options.schools, function(i, school) {
            school_option += '<option value="'+school.id+'">'+school.name+'</option>'
        });
        $('#school').html(school_option);
        $('#div_school').show();
        $('#div_subject').hide();
    }
    if(classification == "Department Head"){
        var subject_option = '<option></option>';
        $.each(options.subjects, function(i, subject) {
            subject_option += '<option value="'+subject.id+'">'+subject.title+'</option>'
        });

        $('#subject').html(subject_option);
        $('#div_subject').show();
        $('#subject').select2({
            multiple: true,
        });
    }
   
    if(
        classification == "District Supervisor" 
    ){
        var district_option = '';
        $.each(options.districts, function(i, district) {
            district_option += '<option value="'+district.id+'">'+district.name+'</option>'
        });
        $('#district').html(district_option);
        $('#div_district').show();
    }

}