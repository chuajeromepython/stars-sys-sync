@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    @can('schools.manage')
        <script type="text/javascript" src="/js/schools.js"></script>
    @endcan
@endsection

@section('content')
	@include('layouts.message')
    @can('schools.manage')
        @include('schools.destroy')
    @endcan
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    @can('schools.manage')
                        <a href="/schools/create" class="btn btn-primary" ><i class="fa fa-plus mr-2"></i> Add School</a>
                    @endcan
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
                                @can('schools.manage')
                                    <th style="width: 10%;">Action</th>
                                @endcan
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
                                    @can('schools.manage')
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
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection