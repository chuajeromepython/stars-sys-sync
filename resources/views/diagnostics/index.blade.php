@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/periodicals.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('diagnostics.upload')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-info"
                        data-toggle="modal"
                        data-target="#upload_modal_assessments">
                        <i class="fa fa-upload mr-2"></i>
                        Upload Diagnostic Test
                    </a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_periodicals">
                        <thead>
                            <tr>
                                <th>Assessment Title</th>
                                <th>Term</th>
                                <th>Grade Level</th>
                                <th>Subject</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments as $assessment)
                                <tr>
                                    <td>{{$assessment->assessment}}</td>
                                    <td>{{$assessment->period}}</td>
                                    <td>{{$assessment->level}}</td>
                                    <td>{{$assessment->subject}}</td>
                                    <th>
                                        <center>
                                            <a href="/diagnostics/{{$assessment->id}}" class="btn bg-purple btn-sm">
                                                <i class="fa fa-list"></i>
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
