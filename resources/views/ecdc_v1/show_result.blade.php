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
		});
	</script>
@endsection

@section('content')
	@include('layouts.message')

    <div class="row">
    	<div class="col-md-12">
    		<div class="card">
    			<div class="card-header">
    				<label class="text-bold text-primary">Student List</label>
    			</div>
    			<div class="card-body">
    				<table class="table table-bordered" id="dt_ecdc">
    					<thead>
    						
    						<tr>
    							<th>LRN</th>
    							<th>Name</th>
    							<th>Age</th>
    							<th>Total Scaled Score</th>
    							<th>Standard Score</th>
    							<th></th>
    						</tr>
    					</thead>
    					<tbody>
    						@foreach($results as $student_id =>  $student)
    							<tr>
    								<td>{{$student['lrn']}}</td>
    								<td>{{$student['name']}}</td>
    								<td>{{$student['age']}}</td> 
    								<td>{{$student['total_scaled_score']}}</td>
    								<td>{{$student['standard_score']}}</td>
    								
    								<td>
    									<a href="/ecdcs/{{$ecdc->id}}/students/{{$student_id}}" target="_blank" class="btn btn-primary  btn-sm">
    										View <i class="fa fa-angle-right"></i> 
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