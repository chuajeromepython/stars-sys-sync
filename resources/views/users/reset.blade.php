<div class="modal fade" tabindex="-1" role="dialog"  id="reset_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/users/reset" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-info"><i class="fa fa-unlock mr-2"></i>Reset Password</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                           <center>
                                <input type="hidden" name="id" id="reset_id">
                                Are you sure you want to reset
                                <span class="text-bold text-info" id="reset_username"></span>
                                 password? <br><br>

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
                    <button type="submit" class="btn btn-info btn-submit">Yes</button>
                </div>
            </form>
        </div>
    </div>
</div>