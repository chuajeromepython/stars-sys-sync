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
                            <label for="file" class="text-muted">Period</label>
                            <select class="select2bs4 form-control" name="period" id="period" required>
                                <option selected disabled>-- Select Period --</option>
                                <option value="1">BoSY - Beginning of School Year</option>
                                <option value="2">MoSY - Mid of School Year</option>
                                <option value="3">EoSY - End of School Year</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="ecdc-upload-date" class="text-muted">Classroom assessment date (optional)</label>
                            <input type="date" name="date" id="ecdc-upload-date" class="form-control" aria-describedby="ecdc-upload-date-help">
                            <small id="ecdc-upload-date-help" class="form-text text-muted">Each student's assessment date comes from row 3 of their P/O/R group: D3, G3, J3, and so on. If this classroom date is blank, the latest student date is used.</small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="file" class="text-muted">Browse ECDC Form</label>
                                <p class="small text-muted">Use a freshly downloaded template. Enter 1 in one P, O, or R column per competency. An unmarked competency saves score "-" with no presentation type. Students without any 1 entries are skipped.</p>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file" name="file">
                                        <label class="custom-file-label" for="file">Choose file</label>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="classroom_id" value="{{$classroom->id}}">
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
