<div class="modal fade" tabindex="-1" role="dialog"  id="create_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/courses/store" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-plus"></i> Add Course</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row"> 
                        <div class="col-md-12">
                            <label class="text-muted">Strand</label>
                            <select class="select2bs4 form-control" name="strand">
                                @foreach($strands as $strand)
                                    <option value="{{$strand->id}}">{{$strand->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted">Course</label>
                            <input type="text" name="course" class="form-control">
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