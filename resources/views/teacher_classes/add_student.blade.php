<div class="modal fade" tabindex="-1" role="dialog"  id="modal_add_student">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/teacher_classes/add_student" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-user-plus mr-2 "></i>Add Student</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <small class="text-danger text-bold"><i>*please search the student you want to add before clicking save</i></small>
                                <label for="file" class="text-muted">Search Student LRN</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="lrn">
                                    <input type="hidden" name="class_id" value="{{$teacher_class->id}}">
                                    <span class="input-group-append">
                                        <button type="button" class="btn btn-outline-success btn-search">Search</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-T">
                            <label for="file" class="text-muted">Status</label>
                            <select class="select2bs4 form-control" name="status">
                                @foreach($statuses as $id => $status)
                                    <option value="{{$id}}">{{$status}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="div-students"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                    <button type="submit" class="btn btn-primary btn-submit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>