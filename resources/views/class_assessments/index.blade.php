@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        var class_assessment_id = {{$class_assessment->id}}
    </script>
    <script type="text/javascript" src="/js/class_assessments.js"></script>
    <style type="text/css">
        .my-select{
            border-radius: 7px;
            border: solid 1px #bdc3c7;
        }
    </style>
@endsection

@section('content')
	@include('layouts.message')
    
    <div class="row mb-2">
        <div class="col-md-3 mb-1">
           <div class="card card-primary card-outline">
                <a href="/class_assessments/{{$class_assessment->id}}/results" target="_blank">
                    <div class="card-body text-center text-bold text-primary">
                        <i class="fa fa-pen mr-2"></i> Assessment Result
                    </div>
                </a>
           </div>
        </div>
        <div class="col-md-3 mb-1">
           <div class="card card-purple card-outline">
                <a href="/class_assessments/{{$class_assessment->id}}/score_analysis" target="_blank">
                    <div class="card-body text-center text-bold text-purple">
                        <i class="fa fa-star mr-2"></i> Score Analysis
                    </div>
                </a>
           </div>
        </div>
        <div class="col-md-3 mb-1">
           <div class="card card-info card-outline">
                <a href="/class_assessments/{{$class_assessment->id}}/item_analysis" target="_blank">
                    <div class="card-body text-center text-bold text-info">
                        <i class="fa fa-chart-line mr-2"></i> Item Analysis
                    </div>
                </a>
           </div>
        </div>
        
        <div class="col-md-3 mb-1">
           <div class="card card-warning card-outline">
                 <a href="/class_assessments/{{$class_assessment->id}}/discrimination_index" target="_blank">
                    <div class="card-body text-center text-bold text-warning">
                        <i class="fa fa-file mr-2"></i> Discrimination Index
                    </div>
                </a>
           </div>
        </div>
        
    </div>

    <div class="row">
        <div class="col-md-12" id="div_answer_key">
            <div class="card">
                <div class="card-header">
                    <label class="text-primary text-bold">
                        <i class="fa fa-list mr-2"></i>
                        Answer keys</label>
                </div>
                <div class="card-body p-0" id="card_answer_key">
                    <table class="table table-bordered">
                        
                        @php $count = 1 @endphp
                        @foreach($assessment_keys as $item_number => $answer)
                            @if($count-1 % 5 == 0)
                                <tr>
                            @endif
                                <td>
                                    <span class="float-left"> {{$item_number}}. </span>
                                    <center>
                                        <span class="badge badge-pill bg-success">
                                            {{$answer}}
                                        </span>
                                    </center>
                                </td>
                            @if($count % 5 == 0)
                                </tr>
                            @endif
                            @php $count++ @endphp
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6" id="div_student" style="display: none;">
            <form method="post" id="edit_student_answers_form" action="/student_answers/batch_update">
                @csrf()
                <div class="card">
                    <div class="card-header">
                        <label class="text-primary text-bold" id="label_student">
                            <i class="fa fa-pen mr-2"></i> Student Answers
                        </label>
                        <input type="hidden" name="assessment_id" value="{{$class_assessment->assessment_id}}">
                        <input type="hidden" name="class_assessment_id" value="{{$class_assessment->id}}">
                        <input type="hidden" name="student_id" id="student_id">
                        <button class="btn btn-primary btn-sm float-right" type="submit">
                            <i class="fa fa-save mr-2"></i> Save
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered"  id="table_student">
                            <tr>
                                <td>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                   <label class="text-primary text-bold">
                        <i class="fa fa-check mr-2"></i>Student Scores</label>
                </div>
                <div class="card-body">
                    <table class="table" id="dt_students">
                        <thead>
                            <tr>
                                <td>LRN</td>
                                <td>Name</td>
                                <td>Score</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                <tr>
                                    <td>{{$student->lrn}}</td>
                                    <td>
                                        {{$student->last_name}},  
                                        {{$student->first_name}} 
                                        {{$student->middle_name}} 
                                        {{$student->suffix}}
                                    </td>
                                    <td>{{$student->score}}</td>
                                    <td>
                                        <a href="#" class="btn btn-primary btn-sm btn-students"
                                            data-id="{{$student->student_id}}"
                                            data-lrn="{{$student->lrn}}"
                                            data-name=" {{$student->last_name}}, {{$student->first_name}}"
                                            >
                                            <i class="fa fa-arrow-right"></i>
                                        </a>
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