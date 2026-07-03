<div class="modal fade" tabindex="-1" role="dialog"  id="destroy_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/academic_years/destroy" class="form-destroy">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-danger"><i class="fa fa-trash"></i> Delete Academic Year</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                       <center>
                            Are you sure you want to delete this?<br>
                            <input type="hidden" name="id" id="destroy_id" class="form-control">
                            <h4 class="text-danger text-bold" id="destroy_label"></h4>
                       </center>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">No</a>
                    <button type="submit" class="btn btn-danger btn-submit-destroy">Yes</button>
                </div>
            </form>
        </div>
    </div>
</div>