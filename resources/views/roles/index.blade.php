@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        var user_management_tab = 'roles';
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
                    @can('roles.manage')
                        <a href="{{ route('roles.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus mr-2"></i> Add Role
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @include('layouts.table-filters', ['filters' => $filters])

                    <table id="dt_user_management" class="table mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Permissions</th>
                                <th>Users</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
