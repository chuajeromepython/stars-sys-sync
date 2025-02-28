<div class="modal fade" tabindex="-1" role="dialog"  id="upload_modal_users">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post"  class="form" id="upload_form" enctype='multipart/form-data'>
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-info"><i class="fa fa-upload"></i>Upload Users</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="file" class="text-muted">Select Classification</label>
                            <select class="select2bs4" name="classification" id="upload_classification">
                                <option>-Select Classification-</option>
                                <option>School Head</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="file" class="text-muted">Browse Uploader</label>
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
                    <button type="submit" class="btn btn-info btn-submit">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>