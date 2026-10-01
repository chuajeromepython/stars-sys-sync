<form method="post" action="{{ $action ?? route('permissions.store') }}" class="form">
        @csrf
        @isset($method)
            @method($method)
        @endisset

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <label class="text-primary"><i class="fa fa-key mr-2"></i> Permission Details</label>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Permission Name</label>
                            <input type="text" name="name" id="name" class="form-control" required
                                maxlength="255" value="{{ old('name', $permission->name ?? '') }}">
                            <small class="form-text text-muted">
                                Use the <code>module.action</code> convention, for example
                                <code>classrooms.manage</code>.
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="guard_name">Guard</label>
                            <select name="guard_name" id="guard_name" class="form-control">
                                <option value="web" @selected(old('guard_name', $permission->guard_name ?? 'web') === 'web')>
                                    web
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fa fa-save mr-2"></i> Save Permission
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>