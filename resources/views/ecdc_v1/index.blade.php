@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
	<script type="text/javascript">
		$(function() {
			$(document).on('change', '#classroom',function(){
				id = $(this).val();
			   	$('.btn-download-ecdc').attr('href', '/ecdcs/'+id+'/download_template');
			   	$('.btn-download-ecdc').removeClass('disabled');
			});

			var screen_width = $(window).width();
		    var screen_height = $(window).height();

		 	$('#dt_ecdc').dataTable({
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
	@include('ecdc.download')
	@include('ecdc.upload')

    <div class="row">
    	<div class="col-md-12">
    		<div class="card">
    			<div class="card-header">
		    		<a href="#" class="btn btn-primary" data-toggle="modal" data-target="#download_modal">
		    			<i class="fa fa-download mr-2"></i> Download ECDC Form
		    		</a>
		    		<a href="#" class="btn btn-info	" data-toggle="modal" data-target="#upload_modal">
		    			<i class="fa fa-upload mr-2"></i> Upload ECDC
		    		</a>
    			</div>
    			<div class="card-body">
    				<table class="table table-bordered" id="dt_ecdc">
    					<thead>
    						<th>Academic Year</th>
    						<th>Grade Level</th>
    						<th>Section</th>
    						<th></th>
    					</thead>
    					<tbody>
    						@foreach($ecdcs as $ecdc)
    							<tr>
    								<td>{{$academic_year->from}} - {{$academic_year->to}}</td>
    								<td>{{$ecdc->level}}</td>
    								<td>{{$ecdc->section}}</td>
    								<td>
    									<center>
	    									<a href="/ecdcs/classroom/{{$ecdc->classroom_id}}" 
	    										class="btn btn-sm btn-info">
	    										 <i class="fa fa-arrow-right"></i>
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