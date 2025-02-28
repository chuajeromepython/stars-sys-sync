@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/subjects.js"></script>
@endsection

@section('content')
    @include('layouts.message')
    @include('subject_components.create')
    @include('subject_components.edit')
    @include('subject_components.destroy')
    <div class="row">
        <div class="col-md-12">
             <div class="card">
            <div class="card-body">
                <center>
                    <h5 class="text-bold text-primary"><i class="fa fa-book mr-3"></i>{{$subject->title}}</h5>
                </center>
            </div>
        </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="/subjects" class="btn btn-danger mr-2">
                        <i class="fa fa-angle-left mr-2"></i> Back
                    </a>
                    <a href="#" class="btn btn-primary" 
                    data-toggle="modal" data-target="#create_modal">
                    <i class="fa fa-plus mr-2"></i> Add Subject Components</a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_subjects">
                        <thead>
                            <tr>
                                <th>Subject Components</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($components as $component)
                                <tr>
                                    <td>{{$component->name}}</td>
                                    <th>
                                        <center>
                                        <a href="#" class="btn-edit btn btn-primary btn-sm" 
                                            data-toggle="modal" data-target="#edit_modal"
                                            data-edit_id="{{$component->id}}" 
                                            data-edit_name="{{$component->name}}">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                            data-toggle="modal" data-target="#destroy_modal"
                                            data-destroy_id="{{$component->id}}" 
                                            data-destroy_name="{{$component->name}}">
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