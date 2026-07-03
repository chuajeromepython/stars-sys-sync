$(function() {

	var screen_width = $(window).width();
	var screen_height = $(window).height();

	$('#dt_answer_keys').dataTable({
	    'language':{
	        'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
	    },
	    "pageLength": 5,
	    'scrollX': (screen_height > screen_width) ? true : false
	});

	$('#dt_csv').dataTable({
	    'language':{
	        'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
	    },
	    "pageLength": 5,
	    'scrollX': (screen_height > screen_width) ? true : false
	});

	$('#dt_periodicals').dataTable({
	    'language':{
	        'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
	    },
	    "pageLength": 5,
	    'scrollX': (screen_height > screen_width) ? true : false
	});

	$("#btn_assessment").click(function() {
        $("#file_assessment").click();
    })
	 
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

    $("#btn_answer_key").click(function() {
        $("#file_answer_key").click();
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

    // Handle edit button click
    $(document).on('click', '.edit-answer-key', function() {
        var questionId = $(this).data('question-id');
        var itemNumber = $(this).data('item-number');
        var question = $(this).data('question');
        var options = $(this).data('options');

        console.log('Question ID:', questionId);
        console.log('Options:', options);

        // Set form action
        $('#edit_answer_key_form').attr('action', '/questions/' + questionId + '/update-answer-key');

        // Set item number and question
        $('#edit_item_number').text(itemNumber);
        $('#edit_question').val(question);

        // Clear previous selections and values
        $('input[name="correct_answer"]').prop('checked', false);
        $('#option_a, #option_b, #option_c, #option_d').val('');
        $('#option_id_a, #option_id_b, #option_id_c, #option_id_d').val('');

        // Populate options - make sure they're in correct order
        if (options && options.length > 0) {
            options.forEach(function(option) {
                var assignment = option.assignment.toUpperCase();
                var assignmentLower = assignment.toLowerCase();
                
                $('#option_' + assignmentLower).val(option.option);
                $('#option_id_' + assignmentLower).val(option.id);

                console.log('Set option_id_' + assignmentLower + ' = ' + option.id);

                // Check if this is the correct answer
                if (option.is_correct == 1) {
                    $('#correct_' + assignmentLower).prop('checked', true);
                }
            });
        }

        // Debug: Check if values are set
        console.log('Option IDs after setting:');
        console.log('A:', $('#option_id_a').val());
        console.log('B:', $('#option_id_b').val());
        console.log('C:', $('#option_id_c').val());
        console.log('D:', $('#option_id_d').val());
    });

    // Handle form submission
    $('#edit_answer_key_form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        var formData = form.serialize();

        console.log('Submitting to:', url);
        console.log('Form data:', formData);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    // Close modal
                    $('#edit_answer_key_modal').modal('hide');
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Answer key updated successfully!');
                    } else {
                        alert(response.message || 'Answer key updated successfully!');
                    }
                    
                    // Reload page after a short delay
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message || 'Failed to update answer key.');
                    } else {
                        alert(response.message || 'Failed to update answer key.');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                var message = 'An error occurred while updating the answer key.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    message = xhr.responseText;
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            }
        });
    });

    // Reset form when modal is closed
    $('#edit_answer_key_modal').on('hidden.bs.modal', function() {
        $('#edit_answer_key_form')[0].reset();
    });
    
});