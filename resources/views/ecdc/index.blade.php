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

    <div class="container">
		<div class="row">
			@foreach($classrooms["Kinder"] as $classroom)
				<div class="col-md-4">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<div class="col-md-12 mb-2">
									<h3 class="card-title text-primary text-bold">
										KINDER - {{$classroom['section']}}
									</h3><br>
									<small><b> Uploaded ECDC </b></small>
								</div>
								<div class="col-4">
									<div class="card">
										<div class="card-body">
											<center>
												<label style="font-size: 28px; font-weight: bolder;">{{$classroom['details']['bosy']}}</label><br>
												<span class="text-secondary">BOSY</span>
											</center>
										</div>
									</div>
								</div>
								<div class="col-4">
									<div class="card">
										<div class="card-body">
											<center>
												<label style="font-size: 28px; font-weight: bolder;">{{$classroom['details']['mosy']}}</label><br>
												<span class="text-secondary">MOSY</span>
											</center>
										</div>
									</div>
								</div>
								<div class="col-4">
									<div class="card">
										<div class="card-body">
											<center>
												<label style="font-size: 28px; font-weight: bolder;">{{$classroom['details']['eosy']}}</label><br>
												<span class="text-secondary">EOSY</span>
											</center>
										</div>
									</div>
								</div>
								<div class="col-12">
									<center>
										<a href="/ecdcs/classroom/{{$classroom["classroom_id"]}}" class="btn btn-rounded btn-primary">MORE INFO</a>
									</center>
								</div>
							</div>
						</div>
					</div>
				</div>
			@endforeach
		</div>       
	</div>
@endsection