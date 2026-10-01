<div class="modal fade" id="edit_answer_key_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fa fa-edit mr-2"></i>Edit Answer Key
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="edit_answer_key_form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle mr-2"></i>
                        <strong>Item Number:</strong> <span id="edit_item_number"></span>
                    </div>

                    <div class="form-group">
                        <label for="edit_question" class="text-bold">
                            <i class="fa fa-question-circle mr-2"></i>Question
                        </label>
                        <textarea 
                            class="form-control" 
                            id="edit_question" 
                            name="question" 
                            rows="3" 
                            placeholder="Enter question text"
                            required></textarea>
                    </div>

                    <hr>
                    <h6 class="text-bold text-primary mb-3">
                        <i class="fa fa-list mr-2"></i>Options (Select the correct answer)
                    </h6>

                    <div id="options_container">
                        <!-- Option A -->
                        <div class="form-group">
                            <div class="card">
                                <div class="card-body">
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" 
                                            class="custom-control-input" 
                                            id="correct_a" 
                                            name="correct_answer" 
                                            value="A" 
                                            required>
                                        <label class="custom-control-label text-bold" for="correct_a">
                                            <span class="badge badge-primary mr-2">A</span> Correct Answer
                                        </label>
                                    </div>
                                    <input type="hidden" name="option_ids[]" id="option_id_a">
                                    <textarea 
                                        class="form-control" 
                                        name="options[]" 
                                        id="option_a" 
                                        rows="2" 
                                        placeholder="Enter option A text"
                                        required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Option B -->
                        <div class="form-group">
                            <div class="card">
                                <div class="card-body">
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" 
                                            class="custom-control-input" 
                                            id="correct_b" 
                                            name="correct_answer" 
                                            value="B" 
                                            required>
                                        <label class="custom-control-label text-bold" for="correct_b">
                                            <span class="badge badge-primary mr-2">B</span> Correct Answer
                                        </label>
                                    </div>
                                    <input type="hidden" name="option_ids[]" id="option_id_b">
                                    <textarea 
                                        class="form-control" 
                                        name="options[]" 
                                        id="option_b" 
                                        rows="2" 
                                        placeholder="Enter option B text"
                                        required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Option C -->
                        <div class="form-group">
                            <div class="card">
                                <div class="card-body">
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" 
                                            class="custom-control-input" 
                                            id="correct_c" 
                                            name="correct_answer" 
                                            value="C" 
                                            required>
                                        <label class="custom-control-label text-bold" for="correct_c">
                                            <span class="badge badge-primary mr-2">C</span> Correct Answer
                                        </label>
                                    </div>
                                    <input type="hidden" name="option_ids[]" id="option_id_c">
                                    <textarea 
                                        class="form-control" 
                                        name="options[]" 
                                        id="option_c" 
                                        rows="2" 
                                        placeholder="Enter option C text"
                                        required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Option D -->
                        <div class="form-group">
                            <div class="card">
                                <div class="card-body">
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" 
                                            class="custom-control-input" 
                                            id="correct_d" 
                                            name="correct_answer" 
                                            value="D" 
                                            required>
                                        <label class="custom-control-label text-bold" for="correct_d">
                                            <span class="badge badge-primary mr-2">D</span> Correct Answer
                                        </label>
                                    </div>
                                    <input type="hidden" name="option_ids[]" id="option_id_d">
                                    <textarea 
                                        class="form-control" 
                                        name="options[]" 
                                        id="option_d" 
                                        rows="2" 
                                        placeholder="Enter option D text"
                                        required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>