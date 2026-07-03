@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/periodicals.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('periodicals.upload_csv')
    

    <a href="/summatives" class="btn btn-danger mr-3 mb-3">
        <i class="fa fa-angle-left mr-2"></i> Back
    </a>
    <div class="row mb-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <label class="text-primary">
                        {{$assessment->period}} Period - Summative Exam No. {{$summative->summative_number}}
                    </label>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-bold mb-1">Title</div>
                        <div class="col-md-6">{{$assessment->assessment}}</div>
                        <div class="col-md-2 text-bold mb-1">Date</div>
                        <div class="col-md-2">{{$assessment->date}}</div>
                        <div class="col-md-2 text-bold mb-1">Subject</div>
                        <div class="col-md-6">{{$assessment->subject}}</div>
                        <div class="col-md-2 text-bold mb-1">Grade Level</div>
                        <div class="col-md-2">{{$assessment->level}}</div>
                        <div class="col-md-2 text-bold mb-1">No. of Items</div>
                        <div class="col-md-2">{{$assessment->number_of_items}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-info" 
                        data-toggle="modal" 
                        data-target="#upload_modal_csv">
                        <i class="fa fa-upload mr-2"></i> 
                        Upload Class Assessment
                    </a>
                </div>
                <div class="card-body">
                    <table class="table" id="dt_csv">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Grade Level</th>
                                <th>Section</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($class_assessments as $class_assessment)
                                <tr>
                                    <td>{{$class_assessment->period}}</td>
                                    <td>{{$class_assessment->level}}</td>
                                    <td>{{$class_assessment->section}}</td>
                                    <td>
                                        <center>
                                            <a href="/class_assessments/{{$class_assessment->id}}"
                                            class="btn btn-primary btn-sm">
                                                RESULT <i class="fa fa-arrow-right ml-2"></i>
                                            </a>
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

    <div class="row mb-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                        <label class="text-bold text-primary">
                            <i class="fa fa-check mr-2"></i>Assessment Answer Keys
                        </label>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-responsive" id="dt_answer_keys">
                        <thead>
                            <tr>
                                <th> Item No. </th>
                                <th> Questions </th>
                                <th> A</th>
                                <th> B</th>
                                <th> C</th>
                                <th> D</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @foreach($answer_keys as $keys)
                            <tr>
                                <td>{{$keys['question']->item_number}}</td>
                                <td>{{$keys['question']->question}}</td>
                                @foreach($keys['options'] as $option)
                                <td>
                                    <span class="badge 
                                        {{ ($option->is_correct == 1) ? 'bg-success' : 'bg-secondary'  }}
                                    mr-2">{{$option->assignment}}</span>
                                    {{$option->option}}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

     
@endsection