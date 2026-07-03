@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        var teachers = @json($teachers);
        var subjects = @json($subjects);
    </script>
    <script type="text/javascript" src="/js/classrooms.js"></script>
@endsection

@section('content')
	
    <div class="container">
        @include('layouts.message')
        <form method="post" action="/classrooms/store" class="form">
            @csrf()
             <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="/classrooms" class="btn btn-danger"><i class="fa fa-angle-left mr-2"></i> Back</a>
                        </div>
                        <div class="card-body" >
                            <div class="row">
                                <div class="col-md-12">
                                    <small class="text-primary text-bold">Classroom Information</small>
                                </div>
                                <div class="mb-1 col-md-12">
                                    <label class="text-muted">School*</label>
                                    <span class="form-control">{{$school->code}} - {{$school->name}}</span>
                                </div>
                                <div class="mb-1 col-md-4">
                                    <label class="text-muted">Academic Year*</label>
                                    <span class="form-control">
                                        {{$academic_year->from}} - {{$academic_year->to}}
                                    </span>
                                </div>
                                <div class="mb-1 col-md-4">
                                    <label class="text-muted">Grade Level*</label>
                                    <select  class="select2bs4 form-control" id="grade_levels" name="grade_level_id" required>
                                        <option selected disabled value="">-Select Grade Level-</option>
                                        @foreach($grade_levels as $grade_level)
                                            <option value="{{$grade_level->id}}">{{$grade_level->level}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-1 col-md-4">
                                    <label class="text-muted">Section*</label>
                                    <select class="select2bs4 form-control" name="section_id" required>
                                        <option selected disabled value="">-Select Section-</option>
                                        @foreach($sections as $section)
                                            <option value="{{$section->id}}">{{$section->section}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row div-shs" style="display: none;">
                                <div class="col-md-12 mb-2">
                                    <hr>
                                    <small class="text-danger text-bold"><i>*For Senior High School Only*</i></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted">Tracks*</label>
                                    <select class="select2bs4 form-control" name="track_id" id="tracks">
                                        <option selected disabled value="">-Select Track-</option>
                                        @foreach($tracks as $track)
                                            <option value="{{$track->id}}">{{$track->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted">Strand*</label>
                                    <select class="select2bs4 form-control" id="strands" name="strand_id">
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted">Course*</label>
                                    <select class="select2bs4 form-control" id="courses" name="course_id">
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted">Semester*</label>
                                      <select class="select2bs4 form-control" name="semester_id" id="semesters">
                                        <option selected disabled value="">-Select semester-</option>
                                        @foreach($semesters as $semester)
                                            <option value="{{$semester->id}}">{{$semester->semester}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <hr>
                                </div>
                            </div>
                            <div class="row">
                                
                                <div class="mb-1 col-md-6">
                                    <label class="text-muted">Class Adviser*</label>
                                    <select class="select2bs4 form-control" name="teachers[]" required>
                                        <option selected disabled value="">-Select Teacher-</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{$teacher->id}}">
                                                {{$teacher->first_name}},
                                                {{$teacher->last_name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-1 col-md-6">
                                    <label class="text-muted">Subject*</label>
                                    <select class="select2bs4 form-control select-subject" name="subjects[]" required>
                                        <option selected disabled value="">-Select Subject-</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{$subject->id}}">
                                                {{$subject->title}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 mt-2 mb-2">
                                    <hr>
                                    <a href="#" class="btn btn-info btn-sm mb-2" id="btn_add_subject_teacher">
                                        <i class="fa fa-plus mr-2"></i>Add Subject Teacher
                                    </a>
                                    <div class="div-subject">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <center>
                                <a class="btn btn-danger btn-cancel"><i class="fa fa-times mr-1"></i>Cancel</a>
                                <button type="submit" class="btn btn-primary btn-submit"><i class="fa fa-check mr-1"></i>Save</button>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection