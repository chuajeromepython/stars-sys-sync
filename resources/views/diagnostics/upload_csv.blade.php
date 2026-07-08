<div class="modal fade" tabindex="-1" role="dialog"  id="upload_modal_csv">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/student_answers/upload" class="form" enctype='multipart/form-data'>
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-info"><i class="fa fa-upload mr-2"></i>Upload Assessment</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label>Select Section</label>
                            <select class="select2bs4 form-control" name="class_id">
                                <option selected disabled>-Select Class-</option>
                                @foreach($rooms as $room)
                                    <option value="{{$room['teacher_class_id']}}">
                                        {{$room['grade_level']}} - {{$room['section']}}
                                    </option>
                                @endforeach
                            </select>
                            <hr>
                        </div>
                        <div class="col-md-12">
                            <center>
                                <img src="/images/assessment_0.png" id="img_assessment"
                                    style="height: 80px;opacity: 20%"><br>
                                <small class="text-muted text-bold mt-3" id="label_assessment">No File Selected</small>
                                <a href="#" class="btn bg-light mt-1 btn-sm btn-block" id="btn_assessment">
                                    Browse Assessment
                                </a>
                            </center>
                            <input type="file" name="file_assessment" id="file_assessment"  style="display: none;">
                            <input type="hidden" name="assessment_id" value="{{$assessment->id}}">
                            <input type="hidden" name="assessment_path" value="diagnostics">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                    <button type="submit" class="btn btn-info btn-submit">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
