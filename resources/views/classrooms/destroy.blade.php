<div class="modal fade" role="dialog"  id="destroy_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/classrooms/destroy" class="form-destroy">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-danger"><i class="fa fa-trash"></i> Delete Classroom</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <center>
                                <input type="hidden" name="classroom_id" id="destroy_classroom_id">
                                Are you sure you want to delete
                                <br><span class="text-bold text-danger" id="destroy_name"></span>
                                ? <br><br>
                                <span class="text-muted"><i>
                                This process cannot be undone. <br>
                                Continue this action?
                               </i> </span> 
                           </center>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">No</a>
                    <button type="submit" class="btn btn-danger btn-submit-destroy">Yes</button>
                </div>
            </form>
        </div>
    </div>
</div>