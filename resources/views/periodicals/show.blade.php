@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/periodicals.js"></script>
    <script>
$(document).ready(function() {
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
</script>
@endsection

@section('content')
	@include('layouts.message')
    @include('periodicals.upload_csv')
    @include('periodicals.edit_answer_key')
    <a href="/summatives" class="btn btn-danger mr-3 mb-3">
        <i class="fa fa-angle-left mr-2"></i> Back
    </a>
    <div class="row mb-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <label class="text-primary">
                        {{$assessment->period}} Period - Periodical Exam
                    </label>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-bold mb-1">Title</div>
                        <div class="col-md-6">{{$assessment->assessment}}</div>
                        <div class="col-md-2 text-bold mb-1">Date</div>
                        <div class="col-md-2">{{$assessment->date}}</div>
                        <div class="col-md-2 text-bold mb-1">Subject</div>
                        <div class="col-md-6">{{$assessment->subject}}</div>
                        <div class="col-md-2 text-bold mb-1">Grade Level</div>
                        <div class="col-md-2">{{$assessment->level}}</div>
                        <div class="col-md-2 text-bold mb-1">No. of Items</div>
                        <div class="col-md-2">{{$assessment->number_of_items}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="/periodicals" class="btn btn-danger float-left mr-3">
                        <i class="fa fa-angle-left mr-2"></i> Back
                    </a>
                    <a href="#" class="btn btn-info" 
                        data-toggle="modal" 
                        data-target="#upload_modal_csv">
                        <i class="fa fa-upload mr-2"></i> 
                        Upload Class Assessment
                    </a>
                </div>
                <div class="card-body">
                    <table class="table" id="dt_csv">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Grade Level</th>
                                <th>Section</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($class_assessments as $class_assessment)
                                <tr>
                                    <td>{{$class_assessment->period}}</td>
                                    <td>{{$class_assessment->level}}</td>
                                    <td>{{$class_assessment->section}}</td>
                                    <td>
                                        <center>
                                            <a href="/class_assessments/{{$class_assessment->id}}"
                                            class="btn btn-primary btn-sm">
                                                <i class="fa fa-arrow-right"></i>
                                            </a>
                                        </center>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                        <label class="text-bold text-primary">
                            <i class="fa fa-check mr-2"></i>Assessment Answer Keys
                        </label>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-responsive" id="dt_answer_keys">
                        <thead>
                            <tr>
                                <th> No. </th>
                                <th> Questions </th>
                                <th> A</th>
                                <th> B</th>
                                <th> C</th>
                                <th> D</th>
                                <th> Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @foreach($answer_keys as $keys)
                            <tr>
                                <td>{{$keys['question']->item_number}}</td>
                                <td>{{$keys['question']->question}}</td>
                                @foreach($keys['options'] as $option)
                                <td>
                                    <span class="badge 
                                        {{ ($option->is_correct == 1) ? 'bg-success' : 'bg-secondary'  }}
                                    mr-2">{{$option->assignment}}</span>
                                    {{$option->option}}</td>
                                @endforeach
                                <td>
                                    <center>
                                        <button class="btn btn-warning btn-sm edit-answer-key"
                                            data-toggle="modal"
                                            data-target="#edit_answer_key_modal"
                                            data-question-id="{{$keys['question']->id}}"
                                            data-item-number="{{$keys['question']->item_number}}"
                                            data-question="{{$keys['question']->question}}"
                                            data-options='@json($keys['options'])'>
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                    </center>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

     
@endsection