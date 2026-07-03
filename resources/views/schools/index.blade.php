@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/schools.js"> </script>
@endsection

@section('content')
	@include('layouts.message')
    @include('schools.destroy')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="/schools/create" class="btn btn-primary" ><i class="fa fa-plus mr-2"></i> Add School</a>
                    {{-- <a href="#" class="btn btn-info" ><i class="fa fa-upload mr-2"></i> Upload School</a> --}}
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-3" id="dt_schools">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>School</th>
                                <th>District</th>
                                <th>Address</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th style="width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schools as $school)
                                <tr>
                                    <td>{{$school->code}}</td>
                                    <td>{{$school->school}}</td>
                                    <td>{{$school->district}}</td>
                                    <td>{{$school->address}}</td>
                                    <td>{{$school->category}}</td>
                                    <td>{{$school->type}}</td>
                                    <th>
                                        <center>
                                            <a href="/schools/{{$school->id}}/edit" class="btn-primary btn-sm btn"><i class="fa fa-pen"></i></a>
                                            <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                                data-toggle="modal" data-target="#destroy_modal"
                                                data-destroy_id="{{$school->id}}" 
                                                data-destroy_name="{{$school->school}}">
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