<div class="modal fade" tabindex="-1" role="dialog"  id="create_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/subject_components/store" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-plus"></i> Add Subject Components</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="text-muted">Parent Subject</label>
                            <label class="form-control">{{$subject->title}}</label>
                            <input type="hidden" name="subject_id" value="{{$subject->id}}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="text-muted">Subject</label>
                            <input type="text" name="name" class="form-control">
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