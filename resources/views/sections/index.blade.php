@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/sections.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('sections.create')
    @include('sections.edit')
    {{-- @include('sections.destroy') --}}
    @include('sections.upload')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#create_modal"><i class="fa fa-plus mr-2"></i> Add Section</a>
                     <a href="#" class="btn btn-info" data-toggle="modal" data-target="#upload_modal_section"><i class="fa fa-upload mr-2"></i> Upload Section</a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_sections" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sections as $section)
                                <tr>
                                    <td>{{$section->section}}</td>
                                    <th>
                                        <center>
                                         <a href="#" class="btn-edit btn btn-primary btn-sm" 
                                            data-toggle="modal" data-target="#edit_modal"
                                            data-edit_id="{{$section->id}}" 
                                            data-edit_name="{{$section->section}}">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        {{-- <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                            data-toggle="modal" data-target="#destroy_modal"
                                            data-destroy_id="{{$section->id}}" 
                                            data-destroy_name="{{$section->section}}">
                                            <i class="fa fa-trash"></i>
                                        </a> --}}
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