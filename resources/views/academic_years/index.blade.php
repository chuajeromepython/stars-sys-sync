@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/academic_years.js"> </script>
@endsection

@section('content')
	@include('layouts.message')
    @include('academic_years.create')
    @include('academic_years.edit')
    @include('academic_years.destroy')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#create_modal"><i class="fa fa-plus mr-2"></i> Add Academic Year</a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_academic_years" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($academic_years as $academic_year)
                                <tr>
                                    <td>{{$academic_year->from}} - {{$academic_year->to}}</td>
                                    <td>
                                        @if($academic_year->is_active == 1)

                                            <span class="badge badge-success">ACTIVE</span>
                                        @else

                                            <span class="badge badge-danger">INACTIVE</span>
                                        @endif
                                    </td>
                                    <th>
                                        <center>
                                        <a  class="btn-primary btn-sm btn btn-edit"
                                            data-toggle="modal" data-target="#edit_modal"
                                            data-from="{{$academic_year->from}}"
                                            data-to="{{$academic_year->to}}"
                                            data-id="{{$academic_year->id}}"
                                            data-status="{{$academic_year->status}}"
                                        >
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <a class="btn-danger btn-sm btn btn-destroy"
                                            data-toggle="modal" data-target="#destroy_modal"
                                            data-from="{{$academic_year->from}}"
                                            data-to="{{$academic_year->to}}"
                                            data-id="{{$academic_year->id}}"
                                            >
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