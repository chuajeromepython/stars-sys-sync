<div class="modal fade" role="dialog"  id="edit_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/users/classifications/{{$user->id}}/update" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-info"><i class="fa fa-pen mr-2"></i> Update Classification</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted">Current Classification</label>
                            <input type="text" disabled id="current_classification" value="{{$user->classification}}" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Update Classification to:</label>
                            <select class="select2bs4 form-control" id="create_classification" name="classification">
                                <option selected disabled>-Select Classification-</option>
                                @foreach($classifications as $classification)
                                    <option>{{$classification->classification}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12" id="div_division" style="display:none;">
                            <label class="text-muted">Division</label>
                            <select class="select2bs4 form-control" name="division" id="division">
                               
                            </select>
                        </div>
                        <div class="col-md-12" id="div_subject" style="display:none;">
                            <label class="text-muted">Subject</label>
                            <select class="select2bs4 form-control" name="subjects[]" id="subject">
                               
                            </select>
                        </div>
                        <div class="col-md-12" id="div_district" style="display:none;">
                            <label class="text-muted">District</label>
                            <select class="select2bs4 form-control" name="district" id="district">
                               
                            </select>
                        </div>
                        @if(Auth::user()->classification == "School Head")
                        <div class="col-md-6">
                            <label class="text-muted">School</label>
                            <input type="hidden" name="school_id" value="{{$options['schools'][0]['id']}}">
                            <input type="text" name="" class="form-control" readonly value="{{$options['schools'][0]['name']}}">
                        </div>
                        @else
                        <div class="col-md-12" id="div_school" style="display:none;">
                            <label class="text-muted">School</label>
                            <select class="select2bs4 form-control" name="school_id" id="school">
                               
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                    <button type="submit" class="btn btn-info btn-submit">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>