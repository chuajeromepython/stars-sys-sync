@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        var strands = @json($strands);
    </script>
    <script type="text/javascript" src="/js/courses.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('courses.create')
    @include('courses.edit')
    @include('courses.destroy')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#create_modal"><i class="fa fa-plus mr-2"></i> Add Course</a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_courses">
                        <thead> 
                            <tr>
                                <th>Strand</th>
                                <th>Course</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $course)
                                <tr>
                                    <td>{{$course->strand}}</td>
                                    <td>{{$course->course}}</td>
                                    <th>
                                        <center>
                                         <a href="#" class="btn-edit btn btn-primary btn-sm" 
                                            data-toggle="modal" data-target="#edit_modal"
                                            data-edit_id="{{$course->id}}"  
                                            data-edit_strand_id="{{$course->strand_id}}"  
                                            data-edit_name="{{$course->course}}">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                            data-toggle="modal" data-target="#destroy_modal"
                                            data-destroy_id="{{$course->id}}" 
                                            data-destroy_name="{{$course->course}}">
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