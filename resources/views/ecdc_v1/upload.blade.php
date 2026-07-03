<div class="modal fade" tabindex="-1" role="dialog"  id="upload_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post"  enctype='multipart/form-data' action="/ecdcs/upload">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-upload"></i> Upload ECDC</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="text-muted">Select Classroom</label>
                            <select class="select2bs4 form-control" id="classroom" name="classroom_id">
                                <option selected value="" disabled>-Select Classroom-</option>
                                @foreach($classrooms as $grade_level => $classrooms)
                                    @if($grade_level == "Kinder")
                                    @foreach($classrooms as $classroom)
                                        <option value="{{$classroom['classroom_id']}}">
                                            {{$grade_level}} - {{$classroom['section']}}
                                        </option>
                                    @endforeach
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                             <div class="form-group">
                                <label for="file" class="text-muted">Browse ECDC Form</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file" name="file">
                                        <label class="custom-file-label" for="file">Choose file</label>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload"> </i>Upload
                    </button>
                </div>
             </form>
        </div>
    </div>
</div>