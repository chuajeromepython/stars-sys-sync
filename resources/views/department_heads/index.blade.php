@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/department_heads.js"></script>

@endsection

@section('content')
	@include('layouts.message')
    @include('department_heads.destroy')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="/department_heads/create" class="btn btn-primary" ><i class="fa fa-plus mr-2"></i> Add Department Head</a>
                   {{--  <a href="/department_heads/create" class="btn btn-info" ><i class="fa fa-upload mr-2"></i> Upload Department Head</a> --}}
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_department_heads">
                        <thead>
                            <tr>
                                <th>Username / Email</th>
                                <th>Name</th>
                                <th>Subjects</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($department_heads as $department_head)
                                <tr>
                                    <td>{{$department_head->username}}</td>
                                    <td>
                                        {{$department_head->first_name}}
                                        {{$department_head->middle_name}}
                                        {{$department_head->last_name}}
                                        {{$department_head->suffix}}
                                    </td>
                                    <td>
                                        @foreach($subjects[$department_head->id] as $subject)
                                            <span class="badge bg-purple">
                                                {{$subject->title}}
                                            </span>
                                        @endforeach
                                    </td>
                                    <th>
                                        <center>

                                        <a href="/department_heads/{{$department_head->id}}/edit" class="btn-primary btn-sm btn"><i class="fa fa-pen"></i></a>

                                        <a href="#" class="btn-danger btn-sm btn btn-destroy"
                                        data-toggle="modal" data-target="#destroy_modal"
                                        data-destroy_id="{{$department_head->user_id}}" 
                                        data-destroy_username="{{$department_head->username}}">
                                        <i class="fa fa-trash"></i></a>

                                       </center>
                                    </th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection