<div class="modal fade" tabindex="-1" role="dialog"  id="upload_modal_sf1">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/students/upload" class="form" enctype='multipart/form-data'>
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-info"><i class="fa fa-plus"></i> Upload SF1 (School Form 1)</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="file" class="text-muted">Browse SF1</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file" name="file">
                                        <label class="custom-file-label" for="file">Choose file</label>
                                        <input type="text" value="{{$classroom->id}}" name="classroom_id">
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