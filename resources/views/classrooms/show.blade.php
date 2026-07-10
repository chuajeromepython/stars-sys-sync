@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    {{-- <script type="text/javascript" src="/js/classrooms.js"></script> --}}
    <script type="text/javascript" src="/js/teacher_classes.js"></script>
@endsection
@section('content')
    @include('layouts.message')
    @include('teacher_classes.upload')
    @include('teacher_classes.create')
    @include('teacher_classes.destroy')

    <div class="row">
        <div class="col-md-12 p-1">
            <div class="col-md-12">
                <a href="/classrooms" class="btn btn-danger mr-2 mb-2">
                    <i class="fa fa-angle-left mr-2"> </i> Back
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="timeline">
                <div class="time-label">
                    <span class="bg-primary">Classroom Details</span>
                </div>
                <div>
                    <i class="fas fa-calendar bg-blue"></i>
                    <div class="timeline-item">
                        <h3 class="timeline-header"><a href="#">Academic Year</a>
                            <br>
                            <span>{{ $academic_year->from }} - {{ $academic_year->to }} </span>
                        </h3>
                    </div>
                </div>
                <div>
                    <i class="fas fa-chair bg-purple"></i>
                    <div class="timeline-item">
                        <h3 class="timeline-header"><a href="#">Grade - Section</a>
                            <br>
                            <span>
                                {{ strtoupper($classroom->level) }} - {{ $classroom->section }}
                            </span>
                        </h3>
                    </div>
                </div>
                <div>
                    <i class="fas fa fa-user-graduate bg-info"></i>
                    <div class="timeline-item">
                        <h3 class="timeline-header">
                            <a href="#"> Total Students </a>
                            <br>
                            <span>
                                {{ $students->count() }}
                            </span>
                        </h3>
                    </div>
                </div>
                <div>
                    <i class="fas fa fa-book bg-green"></i>
                    <div class="timeline-item">
                        <h3 class="timeline-header">
                            <a href="#"> Subject Class</a>
                            <br>
                            <span>
                                {{ $classes->count() }}
                            </span>
                        </h3>
                    </div>
                </div>
                @if ($classroom->grade_level_id == 11 || $classroom->grade_level_id == 12)
                    <div>
                        <i class="fas fa fa-building bg-danger"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">
                                <a href="#">Semester</a>
                                <span class="float-right">
                                    {{ $semester->semester }}
                                </span>
                            </h3>
                        </div>
                    </div>
                    @if ($course != null)
                        <div>
                            <i class="fas fa fa-building bg-danger"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">
                                    <a href="#">Semester</a>
                                    <span class="float-right">
                                        {{ $semester->semester }}
                                    </span>
                                </h3>
                            </div>
                        </div>
                    @endif
                    <div>
                        <i class="fas fa fa-building bg-danger"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">
                                <a href="#">Track and Strand</a>
                                <span class="float-right">
                                    {{ $track->name }} - {{ $strand->name }}
                                </span>
                            </h3>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="mb-2 col-md-8">
            <div class="card">
                <div class="card-header">

                    @if (Auth::user()->classification == 'Teacher')
                        <label class="text-primary"> <i class="fa fa-book mr-3"></i>
                            @if ($is_advisory == 1) Subject Adviser 
                            @else Subject Classes
                            @endif
                        </label>
                    @endif

                    @if (Auth::user()->classification == 'School Head')
                        <a href="#" class="mb-2 btn btn-primary mr-2" id="btn_add" data-toggle="modal"
                            data-target="#create_modal">
                            <i class="fa fa-plus mr-2"></i> Add Subject Classs
                        </a>

                        <a href="#" class="mb-2 btn btn-info" data-toggle="modal"
                            data-target="#upload_modal_subject_class">
                            <i class="fa fa-upload mr-2"> </i> Upload Subject Class
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="dt_classes">
                        <thead>
                            <tr>
                                <th style="width: 50%">Teacher</th>
                                <th style="width: auto">Subject</th>
                                <th style="width: auto">Status</th>
                                 @if ($is_advisory == 1)<th>Action</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classes as $class)
                                <tr>
                                    <td>
                                        {{ $class->first_name }}
                                        {{ $class->middle_name }}
                                        {{ $class->last_name }}
                                        {{ $class->suffix }}
                                    </td>
                                    <td>
                                        {{ $class->title }}
                                    </td>
                                    <td>
                                        @if ($class->advisory == 1)
                                            <span class="badge badge-primary">ADVISORY</span>
                                        @else
                                            <span class="badge badge-info">SUBJECT CLASS</span>
                                        @endif
                                    </td>
                                    @if ($is_advisory == 1)
                                        <td>
                                            <a href="/teacher_classes/{{ $class->id }}" target="_blank"
                                                class="btn btn-primary btn-sm">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            {{-- <a href="#" class="btn btn-danger btn-sm btn-destroy" 
                                            data-toggle="modal"
                                            data-id="{{$class->id}}"
                                            data-subject="{{$class->title}} Class"
                                            data-target="#destroy_modal">
                                        <i class="fa fa-trash"> </i> --}}

                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mb-2">
            <div class="card">
                <div class="card-header">
                    @if ($is_advisory == 1)
                        {{-- <a href="#" class="btn btn-primary"><i class="fa fa-plus mr-2"></i> Add Student</a> --}}
                        <a href="#" class="btn btn-info" data-toggle="modal" data-target="#upload_modal_sf1">
                            <i class="fa fa-upload mr-2"></i> Upload SF1 (School Form 1)
                        </a>
                        @include('students.upload')
                    @else
                        <label class="text-primary"> <i class="fa fa-user-graduate mr-2"></i> Student Lists from SF1
                        </label>
                    @endif
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
                                <td>Source</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                <tr>
                                    <td>{{ $student->lrn }}</td>
                                    <td>
                                        {{ $student->last_name }},
                                        {{ $student->first_name }}
                                        {{ $student->middle_name }}
                                    </td>
                                    <td>{{ $student->gender }}</td>
                                    <td>{{ $student->birth_date }}</td>
                                    <td>
                                        @if ($student->status == 1)
                                            <span class="badge bg-primary">REGULAR</span>
                                        @else
                                            <span class="badge bg-red">DROPPED</span>
                                        @endif

                                    </td>
                                    <td>
                                        @if ($student->is_uploaded == 1)
                                            SF1
                                        @else
                                            ADD
                                        @endif
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
