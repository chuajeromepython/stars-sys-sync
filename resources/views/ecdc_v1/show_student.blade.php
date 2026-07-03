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

		    for (var i = 1; i <=7; i++) {
		    	$('#dt_'+i).dataTable({
			        'language':{
			            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
			        },
			        'pageLength' : 5,
			        'scrollX': (screen_height > screen_width) ? true : false
			    });
		    }
		});
	</script>
@endsection

@section('content')
	@include('layouts.message')

    <div class="container">
    	<div class="row">
	    	<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<label class="text-bold text-primary">
							{{$result['lrn']}} - <span class="text-danger"> {{$result['name']}} </span>
						</label>

						<a href="/ecdcs/{{$ecdc_id}}/students/{{$student_id}}/print_back" 
						target="popup" class="ml-2 btn btn-sm btn-info float-right"></i>Print (Back)
						</a> 
						<a href="/ecdcs/{{$ecdc_id}}/students/{{$student_id}}/print_front" 
						target="_blank" class="btn btn-sm btn-info float-right"></i>Print (Front)
						</a> 
					{{-- 	<a href="#" class="btn btn-sm btn-success float-right">
							<i class="fa fa-download mr-2"> </i>Download
						</a>  --}}
					</div>
					<div class="card-body p-0"  style="overflow:auto">
						<table class="table table-bordered">
							<thead>
							<tr>
								<th class="bg-light">Student</th>
								<td>{{$result['name']}} </span></td>
								<th class="bg-light">LRN</th>
								<td>{{$result['lrn']}}</td>
							</tr>
							<tr>
								<th class="bg-light">Period</th>
								<td>
									{{($ecdc->period == 1) ? "BoSY" : "EoSY"}}
								</td>
								<th class="bg-light">Age</th>
								<td>{{$result['age']}}</td>
							</tr>

							<tr>
								<th class="bg-light">Interpretation</th>
								<td class="text-primary" colspan="3">{{$result['interpretation']}}</td>
							</tr>
							<tr>
								<th class="bg-light" colspan="2">Domain</th>
								<th class="bg-light">Raw Score</th>
								<th class="bg-light">Scaled Score</th>
							</tr>
							</thead>
							<tbody style="height: 300px !important">
								<tr>
									@foreach($domains as $domain)
										<tr>
											<th class="text-{{$colors[$domain->id-1]}}" colspan="2">
												{{$domain->domain}}
											</th>
											<td>
												{{$result['domains'][$domain->id]['score']}}
											</td>
											<td>
												{{$result['domains'][$domain->id]['scaled_score']}}
											</td>
										</tr>
									@endforeach
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
	    	<div class="col-md-12">
	    		<div class="card">
			        <div class="card-header">
			            	<label class="text-bold text-primary">Raw Data</label>
			        </div>
		          	<div class="card-body">
			            <div class="row">
				           <div class="col-md-3">
				                <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">	
				                	@foreach($domains as $domain)
				                   		<a class="nav-link {{ ($domain->id == "1") ? 'active' : '' }} text-bold text-{{$colors[$domain->id-1]}}" 
				                   			id="vert-tabs-{{$domain->id}}-tab" 
				                   			data-toggle="pill" 
				                   			href="#vert-tabs-{{$domain->id}}" role="tab" 
				                   			aria-controls="vert-tabs-{{$domain->id}}" aria-selected="true">
				                   			{{$domain->domain}}
				                   		</a>
				                   	@endforeach
				            	</div>
			              	</div>
				            <div class="col-md-9">
				                <div class="tab-content" id="vert-tabs-tabContent">
					              	@foreach($domains as $domain)
				              		 	<div class="tab-pane text-left fade show {{ ($domain->id == "1") ? 'active' : '' }}" 	id="vert-tabs-{{$domain->id}}" role="tabpanel" aria-labelledby="vert-tabs-{{$domain->id}}-tab">

				              		 		<table class="table table-bordered" id="dt_{{$domain->id}}" style="width: 100%">
												<thead>
													<tr>
														<th class="bg-{{$colors[$domain->id-1]}} text-white" colspan="4">
															<center>{{$domain->domain}}</center>
														</th>
													</tr>
													<tr class="text-{{$colors[$domain->id-1]}}">
														<th >Competency</th>
														<th style="width: 10%;">P</th>
														<th style="width: 10%;">O</th>
														<th style="width: 10%;">R</th>
													</tr>
												</thead>
												<tbody>
													@foreach($data[$domain->id]['competencies'] as $competency)
														<tr>
															<td>{{$competency->competency}}</td>
															<td style="width: 10%;">{{$competency->p}}</td>
															<td style="width: 10%;">{{$competency->o}}</td>
															<td style="width: 10%;">{{$competency->r}}</td>
														</tr>
													@endforeach
												</tbody>
											</table>
					                  	</div>
					              	@endforeach
				                </div>
				            </div>
		            	</div>
		    		</div>
		    	</div>
		    </div>
	    </div>
    </div>
@endsection