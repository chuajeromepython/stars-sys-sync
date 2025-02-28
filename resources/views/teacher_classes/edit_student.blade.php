<div class="modal fade" tabindex="-1" role="dialog"  id="modal_edit">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/teacher_classes/update_student_status" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-pen mr-2 "></i>Edit Student Status</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="text-primary" id="edit_name"></label>
                            <input type="hidden" name="class_id" value="{{$teacher_class->id}}">
                            <input type="hidden" name="student_id" id="edit_student_id">
                            <br>
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