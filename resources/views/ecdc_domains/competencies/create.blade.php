<div class="modal fade" tabindex="-1" role="dialog"  id="create_competency_modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="/ecdc_domains/{{$ecdc_domain->id}}/competencies/store" class="form">
                @csrf()
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-plus"></i> Add Competency</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="text-muted">Domain</label>
                            <input type="text" class="form-control" value="{{$ecdc_domain->domain}}" disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted">Competency</label>
                            <input type="text" name="competency" class="form-control">
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
