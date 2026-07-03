@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
	<script type="text/javascript">
		$(function() {

			var screen_width = $(window).width();
		    var screen_height = $(window).height();

		 	$('#dt_ecdc').dataTable({
		        'language':{
		            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
		        },
		        'scrollX': (screen_height > screen_width) ? true : false
		    });
		    $('#dt_students').dataTable({
		        'language':{
		            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
		        },
		        'scrollX': (screen_height > screen_width) ? true : false
		    });
		    $('#dt_ecdc tbody').on( 'click', '.btn-edit', function () {
        
		        var id = $(this).data('id');
		        var period = $(this).data('period');
		        var date = $(this).data('date');

		        $('#edit_id').val(id);
		        $('#edit_period').val(period).change();
		        $('#edit_date').val(date);
		    });
		});
	</script>
@endsection

@section('content')
	@include('layouts.message')
	
	@include('ecdc.edit')
	
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<label class="text-bold text-primary">List of Uploaded ECDC</label>
				</div>
				<div class="card-body">
					<table class="table table-bordered" id="dt_ecdc">
						<thead>
							
							<tr>
								<th>Upload Date</th>
								<th>Date of Assessment</th>
								<th>Period</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach($ecdcs as $ecdc)
								<tr>
									<td class="text-muted">{{$ecdc->created_at}}</td>
									<td>{{$ecdc->date}}</td>
									<td>
										@if($ecdc->period == 1)
											BoSY
										@else
											EoSY
										@endif
									</td>
									<td>
										<center>
											<a data-toggle="modal" data-target="#edit_modal"
												data-id="{{$ecdc->id}}"
												data-period="{{$ecdc->period}}"
												class="btn btn-sm btn-primary btn-edit">
												<i class="fa fa-pen"></i>
											</a>
											<a  href="/ecdcs/{{$ecdc->id}}" 
												class="btn btn-sm btn-info">
												<i class="fa fa-angle-right ml-1"></i>
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
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<label class="text-bold text-primary">Students ECDC Checklist</label>
				</div>
				<div class="card-body">
					<table class="table table-bordered" id="dt_students">
						<thead>
							<tr>
								<th>LRN</th>
								<th>Name</th>
								<th>BoSY</th>
								<th>EoSY</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach($students as $lrn => $student)
								<tr>
									<td class="text-muted">{{$lrn}}</td>
									<td>{{$student['name']}}</td>
									<td class="text-center">
										@if($student['has_bosy'] == 1)
											<i class="fa fa-check text-success"></i>
										@else
											<i class="fa fa-times text-danger"></i>
										@endif
									</td>
									<td class="text-center">
										@if($student['has_eosy'] == 1)
											<i class="fa fa-check text-success"></i>
										@else
											<i class="fa fa-times text-danger"></i>
										@endif
									</td>
									<td>
										<center>
											<a href="/ecdcs/classroom/{{$classroom->id}}/student_report/{{$student['id']}}" class="btn btn-sm bg-purple {{ ($student['has_bosy'] == 1 && $student['has_eosy'] == 1) ? "" : "disabled" }}">
												Report Card <i class="fa fa-angle-right ml-2"></i>
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
@endsection