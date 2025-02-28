@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
<script type="text/javascript" src="/js/teacher_classes.js"></script>
<script type="text/javascript" src="/js/app.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('teacher_classes.add_student')
    @include('teacher_classes.edit_student')
   
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="/classrooms/{{$teacher_class->classroom_id}}" class="btn btn-danger">
                <i class="fa fa-angle-left mr-2"></i> Back
            </a>
        </div>
    </div>
    <div class="row mb-1">
        <div class="col-md-12">
            <div class="callout callout-info">
                <h5><i class="fa fa-chalkboard-teacher mr-2"></i>  {{$teacher->first_name}} {{$teacher->last_name}}</h5>
            </div>
        </div>
        <div class="mb-2 col-md-3 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fa fa-chair"></i></span>
                <div class="info-box-content">
                    <small class="info-box-text">Classroom Details</small>
                    <span class="info-box-number">
                        {{$classroom->level}} - {{$classroom->section}}
                    </span>
                </div>
            </div>
        </div>
         <div class="mb-2 col-md-5 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fa fa-book"></i></span>
                <div class="info-box-content">
                    <small class="info-box-text">Subject Class Details</small>
                    <span class="info-box-number">
                        {{$subject->title}} 
                    </span>
                </div>
            </div>
        </div>
        <div class="mb-2 col-md-2 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fa fa-user-graduate"></i></span>
                <div class="info-box-content">
                    <small class="info-box-text">Students</small>
                    <span class="info-box-number">
                        {{$students->count()}}
                    </span>
                </div>
            </div>
        </div>
        <div class="mb-2 col-md-2 col-sm-6 col-12">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fa fa-pen"></i></span>
                <div class="info-box-content">
                    <small class="info-box-text">Assessments</small>
                    <span class="info-box-number">
                        0
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <form class="form" method="post" action="/student_classes/sync">
                @csrf()
                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_student">
                    <i class="fa fa-plus mr-2"></i> Add Student
                </a>
                <input type="hidden" name="classroom_id" value="{{$teacher_class->classroom_id}}">
                <input type="hidden" name="class_id" value="{{$teacher_class->id}}">
                <button type="submit" class="btn bg-info btn-submit">
                    <i class="fas fa-users mr-2"></i> Sync Students
                </button>
            </form>
        </div>
    </div>
    @if($students->count() == 0)
        @if($students_classroom->count() == 0)
            <div class="callout callout-danger mt-3">
                <h5>No Student Record!</h5>
                <p>School Form 1 (SF1) is not yet uploaded in this classroom. Please contact the Class Adiviser.</p>
            </div>
        @else
            <div class="callout callout-info mt-3">
                <h5 class="text-info"><i class="fa fa-search mr-2"></i>Student Record Found!</h5>
                <p>School Form 1 (SF1) is already uploaded in this classroom.
                <br> You can load the students in this class by clicking the Sync Students button.
                </p>
            </div>
            {{-- <center>
               
            </center> --}}
        @endif
    @else
        <div class="row mb-3">
            <div class="col-md-12 mb-2">
                <div class="card">
                    <div class="card-header">
                        {{-- 
                        <label class="text-primary"> <i class="fa fa-user-graduate mr-2"></i> Student Lists from SF1 </label> --}}

                        <label class=""><b>Students list</b></label>
                    </div>
                    <div class="card-body">
                        <table class="table" id="dt_students">
                            <thead>
                                <tr>
                                    <td>LRN</td>
                                    <td>Name</td>
                                    <td>Sex</td>
                                    <td>Birthdate</td>
                                    <td>Status</td>
                                    <td></td>
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
                                        </td>
                                        <td>{{$student->gender}}</td>
                                        <td>{{$student->birth_date}}</td>
                                        <td>
                                            @if($student->status == 1)
                                                <span class="badge bg-primary">REGULAR</span>
                                            @elseif($student->status == 2)
                                                <span class="badge bg-success">TRANSFEREE</span>
                                            @elseif($student->status == 3)
                                                <span class="badge bg-danger text-white">BACK SUBJECT</span>
                                            @elseif($student->status == 4)
                                                <span class="badge bg-danger text-white">DROPPED</span>
                                            @else
                                                <span class="badge bg-danger text-white">EXCLUDED</span>    
                                            @endif
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-primary btn-sm btn-edit"
                                                data-toggle="modal" data-target="#modal_edit"
                                                data-student_id="{{$student->id}}"
                                                data-name="{{$student->first_name}} {{$student->last_name}}"
                                            >
                                                <i class="fa fa-pen"></i>
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

    @endif
@endsection