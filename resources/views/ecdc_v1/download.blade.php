<div class="modal fade" role="dialog"  id="download_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <b class="modal-title text-primary"><i class="fa fa-download"></i> Download ECDC Form</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <label class="text-muted">Select Classroom</label>
                        <select class="select2bs4 form-control" id="classroom">
                            <option selected value="" disabled>-Select Classroom-</option>
                            @foreach($classrooms as $grade_level => $classrooms)
                                @if($grade_level == "Kinder")
                                @foreach($classrooms as $classroom)
                                    <option value="{{$classroom['classroom_id']}}">
                                        {{$grade_level}} - {{$classroom['section']}}
                                    </option>
                                @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                <a class="btn btn-primary btn-download-ecdc disabled">Download</a>
            </div>
        </div>
    </div>
</div>