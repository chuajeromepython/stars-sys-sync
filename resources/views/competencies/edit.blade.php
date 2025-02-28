@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript"></script>
@endsection

@section('content')
	@include('layouts.message')
    <form method="post" action="/competencies/update">
        @csrf()
        <div class="card">
            <div class="card-header">
                <a href="/competencies" class="btn btn-danger">
                    <i class="fa fa-angle-left mr-2"> </i>Back
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="text-muted">Code</label>
                        <input type="hidden" name="id" class="form-control" value="{{$competency->id}}">
                        <input type="text" name="code" class="form-control" value="{{$competency->code}}">
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted">Grade Level</label>
                        <select class="select2bs4 form-control" name="grade_level">
                            <option selected disabled>- Select Grade Level -</option>
                            @foreach($grade_levels as $grade_level)
                                <option value="{{$grade_level->id}}" 
                                    {{ ($grade_level->id == $competency->grade_level_id) ? 'selected' : '' }}>

                                    {{$grade_level->level}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted">Subject</label>
                        <select class="select2bs4 form-control" name="subject">
                            <option selected disabled>- Select Subject -</option>
                            @foreach($subjects as $subject)
                                <option value="{{$subject->id}}"
                                {{ ($subject->id == $competency->subject_id) ? 'selected' : '' }}>
                                {{$subject->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 col-md-4">
                        <label class="text-muted">Period</label>
                        <select class="select2bs4 form-control" name="period">
                            <option selected disabled>- Select Period -</option>
                            @foreach($periods as $period)
                                <option value="{{$period->id}}"{{ ($period->id == $competency->period_id) ? 'selected' : '' }}> {{$period->period}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 col-md-4">
                        <label class="text-muted">Week</label>
                        <select class="select2bs4 form-control" name="week">
                            <option selected disabled>- Select Week -</option>
                            @foreach($weeks as $week)
                                <option value="{{$week->id}}" {{ ($week->id == $competency->week_id) ? 'selected' : '' }}>
                                    {{$week->week}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label class="text-muted">Competency Description</label>
                        <textarea class="form-control" rows="6" name="description">{{$competency->description}}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <center>
                    <a href="/competencies/create" class="btn btn-danger">
                        <i class="fa fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check mr-2"></i>Save
                    </button>
                </center>
            </div>
        </div>
    </form>
    
@endsection