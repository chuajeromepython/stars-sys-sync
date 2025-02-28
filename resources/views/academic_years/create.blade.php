<div class="modal fade" tabindex="-1" role="dialog"  id="create_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/academic_years/store" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-plus"></i> Add Academic Year</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted">Year From</label>
                            <input type="number" name="year_from" class=" form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Year To</label>
                            <input type="number" name="year_to" class=" form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                    <button type="submit" class="btn btn-primary btn-submit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>