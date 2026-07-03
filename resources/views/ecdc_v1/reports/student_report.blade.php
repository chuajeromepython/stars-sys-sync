@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
	<script type="text/javascript">
		var results = @json($results);
		var colors  = @json($colors);
		var domains = @json($domains);
	</script>
	<script type="text/javascript" src="/js/ecdcs.js"></script>
@endsection

@section('content')
	@include('layouts.message')
	<div class="row">
    	<div class="col-md-6">
    		<div class="card">
    			<div class="card-header">
		    		<center>
		    			<span class="text-bold text-primary">{{$results['name']}}</span>
		    		</center>
    			</div>
    			<div class="card-body" id="card_body_1" style="overflow: auto">
					<div class="row mb-3">
						{{-- <div class="col-md-4">
							<a href="#" class="btn btn-block btn-outline-info">
								<i class="fa fa-eye mr-2"></i> Preview
							</a>
						</div> --}}
						<div class="col-md-4">
							<a href="#" class="btn btn-block btn-outline-primary">
								<i class="fa fa-print mr-2"></i> Print
							</a>
						</div>
						{{-- <div class="col-md-4">
							<a href="#" class="btn btn-block btn-outline-success">
								<i class="fa fa-download mr-2"></i> Download
							</a>
						</div> --}}
					</div>
    				<table class="table table-bordered" id="dt_ecdc">
    					<thead>
    						<tr>
    							<th class="p-1"></th>
	    						<th class="p-1" colspan="2">BOSY</th>
	    						<th class="p-1" colspan="2">EOSY</th>
    						</tr>
    						<tr>
    							<th class="p-1">Date Tested</th>
    							<td class="p-1" colspan="2">
    								@if($results['bosy']['has_data'] == 1)
    									{{$results['bosy']['date']}}
    								@endif
    							</td>
    							<td class="p-1" colspan="2">
    								@if($results['eosy']['has_data'] == 1)
    									{{$results['eosy']['date']}}
    								@endif
    							</td>
    						</tr>
    						<tr>
    							<th class="p-1">Pupil's Age</th>
    							<td class="p-1" colspan="2">
    								@if($results['bosy']['has_data'] == 1)
    									{{$results['bosy']['data']['age']}}
    								@endif
    							</td>
    							<td class="p-1" colspan="2">
    								@if($results['eosy']['has_data'] == 1)
    									{{$results['eosy']['data']['age']}}
    								@endif
    							</td>
    						</tr>
    						<tr>
    							<th class="p-1">Domains</th>
    							<th class="p-1">Raw Score</th>
    							<th class="p-1">Scaled Score</th>
    							<th class="p-1">Raw Score</th>
    							<th class="p-1">Scaled Score</th>
    						</tr>
    					</thead>
    					<tbody>
    						@foreach($domains as $key => $domain)
    						<tr>
    							<td class="p-1">
    								<a href="#" class="btn bg-{{$colors[$key]}} btn-sm btn-block text-bold btn-domain"
    								data-domain="{{$domain['id']}}">
    									{{$domain['domain']}}
    									<i class="fa fa-angle-right float-right"></i>
    								</a>
    							</td>
    							@if($results['bosy']['has_data'] == 1)
    								<td class="text-center p-1">{{$results['bosy']['data']['domains'][$domain['id']]['score']}}</td>
    								<td class="text-center p-1">{{$results['bosy']['data']['domains'][$domain['id']]['scaled_score']}}</td>
    							@endif
    							@if($results['eosy']['has_data'] == 1)
    								<td class="text-center p-1">{{$results['eosy']['data']['domains'][$domain['id']]['score']}}</td>
    								<td class="text-center p-1">{{$results['eosy']['data']['domains'][$domain['id']]['scaled_score']}}</td>
    							@endif
    						</tr>
    						@endforeach
    						<tr>
    							<th class="p-1">Sum of Scaled Score</th>
    							@if($results['bosy']['has_data'] == 1)
    								<td colspan="2" class="text-center p-1">{{$results['bosy']['data']['total_scaled_score']}}</td>
    							@endif
    							@if($results['eosy']['has_data'] == 1)	
    								<td colspan="2" class="text-center p-1">{{$results['eosy']['data']['total_scaled_score']}}</td>
    							@endif
    						</tr>
    						<tr>
    							<th class="p-1">Standard Score</th>
    							@if($results['bosy']['has_data'] == 1)
    								<td colspan="2" class="text-center p-1">{{$results['bosy']['data']['standard_score']}}</td>
    							@endif
    							@if($results['eosy']['has_data'] == 1)	
    								<td colspan="2" class="text-center p-1">{{$results['eosy']['data']['standard_score']}}</td>
    							@endif
    						</tr>
    						<tr>
    							<th class="p-1">Interpretation</th>
    							@if($results['bosy']['has_data'] == 1)
    								<td colspan="2" class="text-center p-1">{{$results['bosy']['data']['interpretation']}}</td>
    							@endif
    							@if($results['eosy']['has_data'] == 1)	
    								<td colspan="2" class="text-center p-1">{{$results['eosy']['data']['interpretation']}}</td>
    							@endif
    						</tr>
    					</tbody>
    				</table>
    			</div>
    		</div>
    	</div>
    	<div class="col-md-6">
    		<div class="card card-result" style="display:none">
    			<div class="card-header text-bold" id="domain_header">
    				
    			</div>
    			<div class="card-body" id="card_body_2">
    				<table class="table table-bordered" id="dt_raw" style="width: 100%;">
    					<thead>
    						<tr>
    							<th rowspan="2" class="text-center" style="width: 50%;">Competency</th>
    							<th class="text-center" colspan="3" style="width: 20%;">BoSY</th>
    							<th class="text-center" colspan="3" style="width: 20%;">EoSY</th>
    						</tr>
    						<tr>
    							<th>P</th>
	    						<th>O</th>
	    						<th>R</th>
	    						<th>P</th>
	    						<th>O</th>
	    						<th>R</th>
    						</tr>
    					</thead>
    					<tbody id="tbody_raw">
    						
    					</tbody>
    				</table>
    			</div>
    		</div>
    	</div>
	</div>
@endsection