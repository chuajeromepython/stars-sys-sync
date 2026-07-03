@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        

        var screen_width = $(window).width();
        var screen_height = $(window).height();
        $(function() {
            $('#dt_assessments').dataTable({
                'language':{
                    'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
                },
                'scrollX': (screen_height > screen_width) ? true : false
            });
         });
    </script>
@endsection

@section('content')
	@include('layouts.message')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <b class="text-primary"> My Class Assessments</b>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_assessments">
                        <thead>
                            <tr>
                                <th>Assessment Title</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>
                                    <center>
                                        <u>Score</u> <br> Number of Items
                                    </center>
                                </th>
                                <th class="text-center">Percentage</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments as $assessment)
                                <tr>
                                    <td>{{$assessment->assessment}}</td>
                                    <td>{{$assessment->type}} Exam</td>
                                    <td>{{$assessment->subject}}</td>
                                    <td class="text-center">
                                        {{$assessment->score}} / {{$assessment->number_of_items}}
                                    </td>
                                    <td class="text-center">
                                        {{ $assessment->score / $assessment->number_of_items *100 }} %
                                    </td>
                                    <td>
                                        <center>
                                            <a href="/students/class_assessments/{{$assessment->id}}" class="btn btn-primary"><i class="fa fa-angle-right"></i></a>
                                        </center>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection