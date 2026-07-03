@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
	<script type="text/javascript" src="/js/reports/filter.js"></script>
	<script type="text/javascript" src="/js/reports/generate.js"></script>
@endsection
@section('page_css')
	<style type="text/css">
		.display-none{
			display: none;
		}
	</style>
@endsection
@section('content')
@include('layouts.message')
	
    <div class="row">
		<div class="col-md-3">
			<div class="card">
				<div class="card-header">
					<label class="text-primary"> Generate Reports</label>
					@if(Auth::user()->classification != "Teacher")
					<a href="#" class="btn btn-xs bg-primary float-right" data-toggle="dropdown">
						<i class="fa fa-cog"></i>
					</a>
					<div class="dropdown-menu dropdown-menu-lg dropdown-menu-left" style="left: inherit; right: 0px;">
						<span class="dropdown-item dropdown-header">More Options</span>
						<div class="dropdown-divider"></div>
						@if($level >= 4)
						<a href="#" class="dropdown-item" id="btn_show_district">
							<i class="fa fa-map mr-2"></i> District
						</a>
						@endif
						@if($level >= 3)
						<div class="dropdown-divider"></div>
						<a href="#" class="dropdown-item" id="btn_show_school">
							<i class="fas fa-school mr-2"></i> School
						</a>
						@endif
						@if($level >= 2)
						<div class="dropdown-divider"></div>
						<a href="#" class="dropdown-item" id="btn_show_teacher">
						<i class="fas fa-users mr-2"></i> Teacher
						</a>
						@endif
					</div>
					@endif
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-12">
							<small class="text-bold">Education Level</small>
							<select class="select2bs4 form-control" name="education_level" id="education_level">
								<option>-Select Education Level-</option>
								<option value="1">Elementary</option>
								<option value="2">Junior HighSchool</option>
								<option value="3">Senior HighSchool</option>
							</select>
						</div>
						<div class="col-md-12">
							<small class="text-bold">Period</small>
							<select class="select2bs4 form-control" name="period" id="period">
								<option selected value="" disabled>-Select Period-</option>
								@foreach($periods as $period)
									<option value="{{$period->id}}">{{$period->period}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-12">
							<small class="text-bold">Grade Level</small>
							<select class="select2bs4 form-control" name="grade_level" id="grade_level">

							</select>
						</div>
						<div class="col-md-12" id="div_subject">
							<small class="text-bold">Subject</small>
							<select class="select2bs4 form-control" name="subject" id="subject">
								
							</select>
						</div>
						<div class="col-md-12" id="div_assessment_type">
							<small class="text-bold">Assessment Type</small>
							<select class="select2bs4 form-control" name="assessment_type" id="assessment_type">

							</select>
						</div>
						<div class="col-md-12" id="div_report_type">
							<small class="text-bold">Report Type</small>
							<select class="select2bs4 form-control" name="report_type" id="report_type">
								<option value="" selected disabled> -Select Report Type-</option>
								<option value="LC">Level of Competencies</option>
								<option value="AL">Achievement Level</option> 
								<option value="SA">Score Analysis</option>  
							</select>
						</div>
						<div class="col-md-12 display-none" id="div_track">
							<small class="text-bold">Track</small>
							<select class="select2bs4 form-control" name="track" id="track">
								<option selected disabled>-Select Track-</option>
								@foreach($tracks as $track)
									<option value="{{$track->id}}">{{$track->name}}</option>
								@endforeach
							</select>	
						</div>
						<div class="col-md-12 display-none" id="div_strand">
							<small class="text-bold">Strand</small>
							<select class="select2bs4 form-control" name="strand" id="strand">

							</select>
						</div>
						<div class="col-md-12 display-none" id="div_semester">
							<small class="text-bold">Semester</small>
							<select class="select2bs4 form-control" name="semester" id="semester">
								<option selected disabled>-Select Semester-</option>
								@foreach($semesters as $semester)
									<option value="{{$semester->id}}">{{$semester->semester}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-10 display-none  div_district">
							<small class="text-bold">District</small>
							<select class="select2bs4 form-control" name="district" id="district">
								
							</select>
						</div>
						<div class="col-2 display-none  div_district">
							<a href="#" class="btn btn-block  btn-danger mt-4" id="btn_close_district"><i class="fa fa-times"></i></a>
						</div>
						<div class="col-10 display-none div_school">
							<small class="text-bold">Schools</small>
							<select class="select2bs4 form-control" name="school" id="school">
								
							</select>
						</div>
						<div class="col-2  display-none div_school">
							<a href="#" class="btn btn-block  btn-danger mt-4" id="btn_close_school"><i class="fa fa-times"></i></a>
						</div>
						<div class="col-10  display-none div_teacher">
							<small class="text-bold">Teachers</small>
							<select class="select2bs4 form-control" name="teacher" id="teacher">
								
							</select>
						</div>
						<div class="col-2  display-none div_teacher">
							<a href="#" class="btn btn-block  btn-danger mt-4" id="btn_close_teacher"><i class="fa fa-times"></i></a>
						</div>
					</div>
				</div>
				<div class="card-footer">
					<a href="#" class="btn btn-primary btn-block" id="btn_generate">
						<i class="fa fa-file-excel mr-2"> </i>Generate
					</a>
				</div>
			</div>
		</div>
		<div class="col-md-9">
			@include('reports.includes.least_learned')
			@include('reports.includes.achievement_level')
			@include('reports.includes.score_analysis')
		</div>
    </div>

@endsection