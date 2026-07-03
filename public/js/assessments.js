$(function() {

    
    // upload module
    $("#btn_answer_key").click(function() {
        $("#file_answer_key").click();
    })

    $("#btn_assessment").click(function() {
        $("#file_assessment").click();
    })

    $('#file_answer_key').change(function() {

        if ($(this).get(0).files.length > 0) {
            var filename = $(this).val().split('\\').pop();
            $('#label_answer_key').html(filename);
            $('#img_answer_key').attr('src', '/images/checklist_1.png')
            $('#img_answer_key').css('opacity', '100%')
        }else{
            $('#label_answer_key').html("No File Selected.");
            $('#img_answer_key').attr('src', '/images/checklist_0.png')
            $('#img_answer_key').css('opacity', '20%')
        }
        
    });
    
    $('#file_assessment').change(function() {
        if ($(this).get(0).files.length > 0) {
            var filename = $(this).val().split('\\').pop();
            $('#label_assessment').html(filename);
            $('#img_assessment').attr('src', '/images/assessment_1.png')
            $('#img_assessment').css('opacity', '100%')
        }else{
            $('#label_assessment').html("No File Selected.");
            $('#img_assessment').attr('src', '/images/assessment_0.png')
            $('#img_assessment').css('opacity', '20%')
        }
    });



})