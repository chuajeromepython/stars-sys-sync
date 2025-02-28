<div class="modal fade" tabindex="-1" role="dialog"  id="edit_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post"  enctype='multipart/form-data' action="/ecdcs/update">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-pen"></i> Edit ECDC</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" name="id" class="form-control" id="edit_id">
                            <label class="text-muted">Period</label>
                            <select class="select2bs4 form-control" id="edit_period" name="period">
                                <option value="1">BoSY</option>
                                <option value="2">MoSY</option>
                                <option value="3">EoSY</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted">Date</label>
                            <input type="date" name="date" id="edit_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload"> </i>Update
                    </button>
                </div>
             </form>
        </div>
    </div>
</div>