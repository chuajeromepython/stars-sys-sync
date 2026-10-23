@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    @can('districts.manage')
        <script type="text/javascript" src="/js/districts.js"></script>
        <script>
            $(document).on('click', '.btn-edit', function () {
                $('#edit_modal select[name="division_id"]')
                    .val($(this).data('edit_division_id'))
                    .trigger('change');
            });
        </script>
    @endcan
@endsection

@section('content')
	@include('layouts.message')
    @can('districts.manage')
        @include('districts.create')
        @include('districts.edit')
        @include('districts.destroy')
    @endcan
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    @can('districts.manage')
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#create_modal"><i class="fa fa-plus mr-2"></i> Add District</a>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-3" id="dt_districts" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>District</th>
                                @can('districts.manage')
                                    <th>Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($districts as $district)
                                <tr>
                                    <td>{{$district->name}}</td>
                                    @can('districts.manage')
                                        <th>
                                        <center>
                                        <a href="#" class="btn-edit btn btn-primary btn-sm" 
                                            data-toggle="modal" data-target="#edit_modal"
                                            data-edit_id="{{$district->id}}" 
                                            data-edit_name="{{$district->name}}"
                                            data-edit_division_id="{{$district->division_id}}">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                            data-toggle="modal" data-target="#destroy_modal"
                                            data-destroy_id="{{$district->id}}" 
                                            data-destroy_name="{{$district->name}}">
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
