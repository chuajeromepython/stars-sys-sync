@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/semesters.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('semesters.create')
    @include('semesters.edit')
    @include('semesters.destroy')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#create_modal"><i class="fa fa-plus mr-2"></i> Add Semester</a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_semesters"  style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Semester</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($semesters as $semester)
                                <tr>
                                    <td>{{$semester->semester}}</td>
                                    <th>
                                        <center>
                                         <a href="#" class="btn-edit btn btn-primary btn-sm" 
                                            data-toggle="modal" data-target="#edit_modal"
                                            data-edit_id="{{$semester->id}}" 
                                            data-edit_name="{{$semester->semester}}">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                            data-toggle="modal" data-target="#destroy_modal"
                                            data-destroy_id="{{$semester->id}}" 
                                            data-destroy_name="{{$semester->semester}}">
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