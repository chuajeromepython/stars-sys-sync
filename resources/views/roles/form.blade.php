@php
    $editingRole = $role ?? null;
    $selected = old(
        'permissions',
        $editingRole ? $editingRole->permissions->pluck('name')->all() : []
    );
@endphp

<form method="post" action="{{ $action ?? route('roles.store') }}" class="form">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <label class="text-primary"><i class="fa fa-user-tag mr-2"></i> Role Details</label>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Role Name</label>
                        <input type="text" name="name" id="name" class="form-control" required
                            maxlength="255" value="{{ old('name', $editingRole?->name) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <label class="text-primary"><i class="fa fa-key mr-2"></i> Permissions</label>
                </div>
                <div class="card-body" style="overflow: auto; max-height: 28rem;">
                    @foreach ($modules as $module => $permissions)
                        <div class="card mb-2">
                            <div class="card-header py-2">
                                <label class="mb-0">
                                    <input type="checkbox" class="module-toggle"
                                        data-module="{{ $module }}"> <b>{{ str($module)->headline() }}</b>
                                </label>
                            </div>
                            <div class="card-body py-2">
                                <div class="row">
                                    @foreach ($permissions as $permission)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input module-item"
                                                    data-module="{{ $module }}" name="permissions[]"
                                                    value="{{ $permission }}" id="perm-{{ $module }}-{{ $permission }}"
                                                    @checked(in_array($permission, (array) $selected, true))>
                                                <label class="form-check-label"
                                                    for="perm-{{ $module }}-{{ $permission }}">
                                                    {{ $permission }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer">
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary float-right">
                        <i class="fa fa-save mr-2"></i> Save Role
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('.module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            document.querySelectorAll('.module-item[data-module="' + toggle.dataset.module + '"]')
                .forEach(function (item) { item.checked = toggle.checked; });
        });
    });
</script>
