@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/assessments.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('assessments.upload')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-info" 
                        data-toggle="modal" 
                        data-target="#upload_modal_assessments">
                        <i class="fa fa-upload mr-2"></i> 
                        Upload Assessments
                    </a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_courses">
                        <thead>
                            <tr>
                                <th>Assessment Title</th>
                                <th>Period</th>
                                <th>Grade Level</th>
                                <th>Subject</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments as $assessment)
                                <tr>
                                    <td>{{$assessment->assessment}}</td>
                                    <td>{{$assessment->period}}</td>
                                    <td>{{$assessment->level}}</td>
                                    <td>{{$assessment->level}}</td>
                                    <th>
                                        <center>
                                         <a href="#" class="btn-edit btn btn-primary btn-sm" 
                                            data-toggle="modal" data-target="#edit_modal"
                                            data-edit_id="{{$assessment->id}}"  
                                            data-edit_strand_id="{{$assessment->strand_id}}"  
                                            data-edit_name="{{$assessment->assessment}}">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                            data-toggle="modal" data-target="#destroy_modal"
                                            data-destroy_id="{{$assessment->id}}" 
                                            data-destroy_name="{{$assessment->assessment}}">
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