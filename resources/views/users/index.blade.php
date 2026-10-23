@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        var user_management_tab = 'users';
    </script>
    <script type="text/javascript" src="/js/users/users.js"></script>
    <script type="text/javascript" src="/js/user-management.js"></script>
@endsection

@section('content')

	@include('layouts.message')
    @include('layouts.user-management-tabs')
    @include('users.reset')
    @include('users.destroy')
    @include('users.upload')
    @include('users.roles')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <a href="/users/create" class="btn btn-primary"><i class="fa fa-plus mr-2"></i> Add User</a>
                    <a href="#" class="btn btn-info ml-2" data-toggle="modal"
                        data-target="#upload_modal_users"><i class="fa fa-upload mr-2"></i> Upload Users</a>
                </div>
                <div class="card-body">
                    @include('layouts.table-filters', ['filters' => $filters])

                    <table class="table mb-0 w-100" id="dt_users">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Area</th>
                                <th>Roles</th>
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