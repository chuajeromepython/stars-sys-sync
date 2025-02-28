<div class="modal fade" role="dialog"  id="edit_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/classrooms/update" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-plus"></i> Edit Classroom</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="overlay">
                        <i class="fas fa-2x fa-sync-alt spinner"></i>
                    </div>
                    <div class="row">
                        <input type="hidden" name="classroom_id" id="edit_classroom_id">
                        <div class="col-md-12">
                            <label class="text-muted">Section</label>
                            <label class="float-right edit-section" ></label>
                            <select class="select2bs4 form-control" id="edit_section" name="section_id">
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted">Advisor</label>
                            <label class="float-right edit-teacher" ></label>
                            <select class="select2bs4 form-control" id="edit_teacher" name="teacher_id">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                    <button type="submit" class="btn btn-primary btn-submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>