@can('roles.assign')
    <div class="modal fade" tabindex="-1" role="dialog" id="roles_modal">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="post" id="roles_form" class="form">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <b class="modal-title text-success">
                            <i class="fa fa-user-tag mr-2"></i>Assign Roles
                        </b>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1">
                            Account: <span class="text-bold text-info" id="roles_username"></span>
                        </p>
                        <p class="text-muted mb-3">
                            A user may hold several roles at once. Access is the union of the permissions
                            granted by every selected role. Ticking a role adds it, unticking removes it.
                        </p>

                        <div id="roles_current" class="mb-3">
                            <label class="mb-1"><b>Currently assigned</b></label>
                            <div id="roles_current_list">
                                <span class="text-muted">Loading…</span>
                            </div>
                        </div>

                        <label for="roles_select" class="mb-1"><b>Add or remove roles</b></label>
                        <select id="roles_select" name="roles[]" multiple size="8"
                            class="form-control" data-placeholder="Select roles">
                        </select>
                        <small class="form-text text-muted">
                            Hold Ctrl (Cmd on macOS) to select more than one role.
                        </small>
                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save mr-2"></i> Save Roles
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan