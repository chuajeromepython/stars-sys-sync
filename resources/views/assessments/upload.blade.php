<div class="modal fade" tabindex="-1" role="dialog"  id="upload_modal_assessments">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/assessments/upload" class="form" enctype='multipart/form-data'>
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
                            <label>Select Class</label>
                            <select class="select2bs4 form-control" name="class_id">
                                <option selected disabled>-Select Class-</option>
                                @foreach($classes as $class)
                                    <option value="{{$class->id}}">
                                        {{$class->level}} - {{$class->section}} 
                                        ( {{$class->title}} )
                                    </option>
                                @endforeach
                            </select>
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <center>
                                <img src="/images/checklist_0.png" id="img_answer_key"  
                                    style="width:70%; opacity: 20%"><br>
                                <small class="text-muted text-bold mt-3" id="label_answer_key">No File Selected</small>
                                <a href="#" class="btn bg-light mt-1 btn-sm btn-block" id="btn_answer_key">
                                    Browse Answer Key
                                </a>
                            </center>
                            <input type="file" name="file_answer_key" id="file_answer_key" style="display: none;">
                        </div>
                        <div class="col-md-6">
                            <center>
                                <img src="/images/assessment_0.png" id="img_assessment"  
                                    style="width:70%; opacity: 20%"><br>
                                <small class="text-muted text-bold mt-3" id="label_assessment">No File Selected</small>
                                <a href="#" class="btn bg-light mt-1 btn-sm btn-block" id="btn_assessment">
                                    Browse Assessment
                                </a>
                            </center>
                            <input type="file" name="file_assessment" id="file_assessment"  style="display: none;">
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