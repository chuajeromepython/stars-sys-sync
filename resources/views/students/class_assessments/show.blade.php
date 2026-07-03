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
                'pageLength' : 5,
                'scrollX': (screen_height > screen_width) ? true : false
            });
         });
    </script>
@endsection

@section('content')
	@include('layouts.message')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-light">
                <div class="inner">
                    <b class="text-warning">My Score</b>
                    <h3>{{$result['score']}} / {{$assessment['number_of_items']}}</h3>
                </div>
                <div class="icon p-2 text-warning">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-light">
                <div class="inner">
                    <b class="text-primary">Percentage</b>
                    <h3>{{$result['percentage']}}</h3>
                </div>
                <div class="icon p-2 text-primary">
                    <i class="fas fa-percent"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-light">
                <div class="inner">
                    <b class="text-info">Proficiency</b>
                    <h3>{{$result['proficiency']}}</h3>
                </div>
                <div class="icon p-2 text-info">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-light">
                <div class="inner">
                    <b class="text-purple">Achievement Lvl.</b>
                    <h3>{{$result['achievement']}}</h3>
                </div>
                <div class="icon p-2 text-purple">
                    <i class="fas fa-pen"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center">
                    <b class="text-primary">{{$assessment->title}} Result</b>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_assessments">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Competency</th>
                                <th>Correct Answers</th>
                                <th>Total Items</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($competencies as $code => $competency)
                                <tr>
                                    <th>{{$code}}</th>
                                    <td>{{$competency['description']}}</td>
                                    <td class="text-center">{{$competency['total_corrects']}}</td>
                                    <td class="text-center">{{sizeof($competency['items'])}}</td>
                                    <td class="text-center">
                                        {{ number_format((float)$competency['total_corrects'] / sizeof($competency['items']) *100, 2, '.', '')}} %
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