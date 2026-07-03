$(function() {

	$('#btn_generate').click(function(){
        generate();
    });

})


function generate(){
    var errors = validateFields();
    if(errors.length > 0){
        for (var i = 0; i < errors.length; i++) {
            toastr.error(errors[i]);
        }
    }else{
        $.ajax({
            url: '/reports/generate',
            type: "POST",
            data: {
                "education_level_id" : $('#education_level').val(),
                "period_id" : $('#period').val(),
                "grade_level_id" : $('#grade_level').val(),
                "subject_id" : $('#subject').val(),
                "assessment_type_id" : $('#assessment_type').val(),
                "report_type_id" : $('#report_type').val(),
                "track_id" : $('#track').val(),
                "strand_id" : $('#strand').val(),
                "semester_id" : $('#semester').val(),
                "district_id" : $('#district').val(),
                "school_id" : $('#school').val(),
                "teacher_id" : $('#teacher').val()
            },
            success: function(data){
                if ($('#report_type').val() == "LC") {
                    generateLevelofCompetencies(data);
                }
                if ($('#report_type').val() == "AL") {
                    generateAchievementLevel(data);
                }
                if ($('#report_type').val() == "SA") {
                    generateScoreAnalysis(data);
                }

                toastr.info("Report Successfully Generated")
            },
        });
    }
}

function generateLevelofCompetencies(data){

    $('#dt_least_learned').dataTable().fnDestroy();
    $('.div_LC').fadeIn();
    var html = "";    
    $.each(data, function(key, item) {
        html += '<tr>\
            <th>'+key+'</th>\
            <td>'+data[key].description+'</td>\
            <td>'+data[key].percentage+'</td>\
            <th>'+data[key].mastery+'</th>\
        </tr>';
    });
    // redeclare datatable
    $('#dt_least_learned_tbody').html(html);
    $('#dt_least_learned').dataTable({
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'pageLength' : 5,
        'scrollX': true
    });
}

function generateAchievementLevel(data){
    
    var html = "";
    var references = ["total","M","CAM","MTM","AVR","L","VL","ANM"];

    $('#dt_achievement_level').dataTable().fnDestroy();
    $('.div_AC').fadeIn();

    $.each(data.data, function(name, achievements) {
        html += "<tr>";
           html+="<td>"+name+"</td>";
        for (var i = 0; i < references.length; i++) {
            if (references[i] == "total") {
                html+="<td>"+achievements[references[i]]+"</td>";
            }else{
                var count = (references[i] in achievements) ? achievements[references[i]]['count'] : 0;
                var percentage = (references[i] in achievements) ? achievements[references[i]]['percentage']+"%" : "0.00%";
                html+="<td>"+count+"</td>";
                html+="<td>"+percentage+"</td>";
            }
           
        }
        html += "</tr>";
    });

    $('#header_achievement_level').html(data.header)
    $('#dt_achievement_level_tbody').html(html)
    $('#dt_achievement_level').dataTable({
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'pageLength' : 5,
        'scrollX': true
    });
}


function generateScoreAnalysis(data){
    
    $('.div_SC').fadeIn();

    var total_proficiency = 0;
    var total_mean = 0;
    var total_mps = 0;
    var total_sd = 0;
    var total_apg = 0;
    var total_hpg = 0;
    var total_lpg = 0;
    var html = "";


    $.each(data.data, function(name, scores) {
        html += "<tr>";
           html+="<th>"+name+"</th>";
           html+="<td>"+scores.proficiency+"</td>";
           html+="<td>"+scores.mean+"</td>";
           html+="<td>"+scores.mps+"</td>";
           html+="<td>"+scores.sd+"</td>";
           html+="<td>"+scores.hpg+"</td>";
           html+="<td>"+scores.apg+"</td>";
           html+="<td>"+scores.lpg+"</td>";
       
        html += "</tr>";

        total_proficiency += parseInt(scores.proficiency);
        total_mean +=  parseInt(scores.mean);
        total_sd +=  parseInt(scores.sd);
        total_mps +=  parseInt(scores.mps);
        total_hpg +=  parseInt(scores.hpg);
        total_lpg +=  parseInt(scores.lpg);
        total_apg +=  parseInt(scores.apg);
    });

    html_total = "";
    html_total+="<th class='text-danger'>Total</th>";
    html_total+="<th class='text-danger'>"+total_proficiency/data.size+"</th>";
    html_total+="<th class='text-danger'>"+total_mean/data.size+"</th>";
    html_total+="<th class='text-danger'>"+total_mps/data.size+"</th>";
    html_total+="<th class='text-danger'>"+total_sd/data.size+"</th>";
    html_total+="<th class='text-danger'>"+total_hpg+"</th>";
    html_total+="<th class='text-danger'>"+total_apg+"</th>";
    html_total+="<th class='text-danger'>"+total_lpg+"</th>";

    $('#header_score_analysis').html(data.header);
    $('#row_total').html(html_total);
    $('#dt_score_analysis_tbody').html(html);
    let table = $('#dt_score_analysis').DataTable().destroy();
    table.rows().remove();
    $('#dt_score_analysis').DataTable({
        'language':{
            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
        },
        'pageLength' : 5,
        'scrollX': true
    });
    table.draw();
}

function validateFields(){

    var errors             = [];
    var education_level_id = $('#education_level').val();
    var period_id          = $('#period').val();
    var grade_level_id     = $('#grade_level').val();
    var subject_id         = $('#subject').val();
    var assessment_type_id = $('#assessment_type').val();
    var report_type_id     = $('#report_type').val();
    var track_id           = $('#track').val();
    var strand_id          = $('#strand').val();

    if(education_level_id == null){
        errors.push("Education Level cannot be null.");
    }else{
        if(education_level_id == 3){
            if(track_id == null){
                errors.push("Track cannot be null.");
            }
        if(strand_id == null){
                errors.push("Strand cannot be null.");
            }
        }
    }
    if(period_id == null){
        errors.push("Period cannot be null.");
    }
    if(grade_level_id == null){
        errors.push("Grade Level cannot be null.");
    }
    if (grade_level_id < 13) {
        if (subject_id == null) {
            errors.push("Subject cannot be null");
        }
    }
    if(assessment_type_id == null){
        errors.push("Assessment Type cannot be null.");
    }
    if(report_type_id == null){
        errors.push("Report Type cannot be null.");
    }

    return errors;


}