<div class="modal fade" role="dialog"  id="create_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/teacher_classes/store" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-plus"></i> Add Subject Class</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="classroom_id" value="{{$classroom->id}}">
                        <div class="col-md-12">
                            <label class="text-muted">Teacher</label>
                            <select class="select2bs4 form-control" name="teacher_id" id="teachers">
                                @foreach ($teachers as $teacher)
                                    <option value="{{$teacher->id}}">
                                        {{$teacher->first_name}} 
                                        {{$teacher->middle_name}} 
                                        {{$teacher->last_name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted">Subject</label>
                            <select class="select2bs4 form-control" name="subject_id">
                                <option selected disabled>-Select Subject-</option>
                                @foreach($subjects as $subject)
                                    <option value="{{$subject->id}}">{{$subject->title}}</option>
                                @endforeach
                            </select>
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