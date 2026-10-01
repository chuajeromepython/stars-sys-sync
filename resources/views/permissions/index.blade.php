@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        var user_management_tab = 'permissions';
    </script>
    <script type="text/javascript" src="/js/user-management.js"></script>
@endsection

@section('content')
    @include('layouts.message')
    @include('layouts.user-management-tabs')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex">
                    @can('permissions.manage')
                        <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus mr-2"></i> Add Permission
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @include('layouts.table-filters', ['filters' => $filters])

                    @if (count($missing) > 0)
                        <div class="alert alert-warning">
                            <strong>{{ count($missing) }} permission(s) declared in the matrix are not
                                provisioned yet.</strong>
                            Run <code>php artisan rbac:sync</code> to create them.
                        </div>
                    @endif

                    <table id="dt_user_management" class="table mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Permission</th>
                                <th class="text-center">Roles</th>
                                <th style="width: 8rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
