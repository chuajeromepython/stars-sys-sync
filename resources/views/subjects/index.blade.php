@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/subjects.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('subjects.create')
    @include('subjects.edit')
    @include('subjects.destroy')
    <div class="row">
        
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#create_modal"><i class="fa fa-plus mr-2"></i> Add Subject</a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_subjects">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                                <tr>
                                    <td>{{$subject->id}}</td>
                                    <td>{{$subject->title}}</td>
                                    <th style="width: 40%;">
                                        <center>
                                        <a href="/subjects/{{$subject->id}}" class="btn bg-purple btn-sm">
                                            <i class="fa fa-cog"></i>
                                        </a>
                                        <a href="#" class="btn-edit btn btn-primary btn-sm" 
                                            data-toggle="modal" data-target="#edit_modal"
                                            data-edit_id="{{$subject->id}}" 
                                            data-edit_name="{{$subject->title}}">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                            data-toggle="modal" data-target="#destroy_modal"
                                            data-destroy_id="{{$subject->id}}" 
                                            data-destroy_name="{{$subject->title}}">
                                            <i class="fa fa-trash"></i>
                                        </a>
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