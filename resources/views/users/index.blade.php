@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/users/users.js"></script>
@endsection

@section('content')

	@include('layouts.message')
    @include('users.reset')
    @include('users.destroy')
    @include('users.upload')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="/users/create" class="btn btn-primary"><i class="fa fa-plus mr-2"></i> Add User</a>
                     <a href="#" class="btn btn-info"  data-toggle="modal" 
                        data-target="#upload_modal_users"><i class="fa fa-upload mr-2"></i> Upload Users</a>
                </div>
                <div class="card-body" >
                    @include('users.search')
                    <div style="overflow: auto;">
                        <table class="table table-bordered mb-3" id="dt_users">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Classification</th>
                                    <th>Area</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($users->count() == 0)
                                    <tr>
                                        <td colspan="4">
                                            <center> <span class="badge bg-danger"> No Result Found.</span></center>
                                        </td>
                                    </tr>
                                @else
                                    @foreach($users as $user)
                                        <tr>
                                            <th>
                                                <a href="/users/{{$user->id}}" class="text-purple">
                                                  
                                                    {{$user->first_name}}
                                                    {{$user->middle_name}}
                                                    {{$user->last_name}}
                                                    
                                                    <i class="fa fa-angle-right ml-2"></i>
                                                </a>
                                            </th>
                                            <td>{{$user->username}}</td>
                                            <td>{{$user->classification}}</td>
                                            <td>{{$user->area}}</td>
                                            <td>
                                               <center>

                                                <a href="/users/{{$user->id}}/edit" class="btn-primary btn-sm btn"><i class="fa fa-pen"></i></a>
                                               
                                                <a href="#" class="btn btn-info btn-sm btn-reset" 
                                                    data-toggle="modal" data-target="#reset_modal"
                                                    data-reset_id="{{$user->id}}" 
                                                    data-reset_username="{{$user->username}}">
                                                    <i class="fa fa-unlock"></i></a>

                                                <a href="#" class="btn-danger btn-sm btn btn-destroy"
                                                    data-toggle="modal" data-target="#destroy_modal"
                                                    data-destroy_id="{{$user->id}}" 
                                                    data-destroy_username="{{$user->username}}">
                                                    <i class="fa fa-trash"></i></a>
                                               </center>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    {{ $users->appends(request()->input())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection