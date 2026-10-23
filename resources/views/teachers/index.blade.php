@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/teachers.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    {{-- @include('teachers.destroy') --}}
    @include('teachers.upload')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="/teachers/create" class="btn btn-primary" ><i class="fa fa-plus mr-2"></i> Add Teacher</a>
                    <a href="#" class="btn btn-info" 
                        data-toggle="modal" 
                        data-target="#upload_modal_teachers">
                        <i class="fa fa-upload mr-2"></i> 
                        Upload Teacher
                    </a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_teachers">
                        <thead>
                            <tr>
                                <th>Username / Email</th>
                                <th>Name</th>
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