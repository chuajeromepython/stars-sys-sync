@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
	<script type="text/javascript">
		$(function() {
			var screen_width = $(window).width();
		    var screen_height = $(window).height();
		    var table_width = $('.table').width();
		});
	</script>
	<style>
		table,
		thead,
		tbody,
		tr {
		  display: block;
		}
		thead {
		  margin-right:1em;
		}
		tr {
		  display: table;
		  table-layout: fixed;
		  width:100%;
		}
		tbody {
		  height: 350px;
		  overflow: auto ; /* eventually : scroll;*/
		}
	</style>
@endsection

@section('content')
	@include('layouts.message')

	<div class="row">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header">
					<label class="text-bold text-primary">
					</label>
				</div>
				<div class="card-body p-0">
					<table class="table table-bordered">
						<thead>
							<tr>
							<th>Period</th>
							<td>
								{{($ecdc->period == 1) ? "BoSY" : "EoSY"}}
							</td>
							<th>Age</th>
							<td>{{$result['age']}}</td>
						</tr>
						<tr>
							<th colspan="2">Domain</th>
							<th>Raw Score</th>
							<th>Scaled Score</th>
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
		@foreach($data as $domain_id => $row)
			<div class="col-md-6">
				<div class="card">
					<div class="card-header bg-{{$row['color']}}">
						<label class="text-bold text-white">
							{{$row['domain']}}
						</label>
					</div>
					<div class="card-body p-0">
						<table class="table table-bordered tableFixHead"  >
							<thead>
								<tr class="text-{{$row['color']}}">
									<th >Competency</th>
									<th style="width: 10%;">P</th>
									<th style="width: 10%;">O</th>
									<th style="width: 10%;">R</th>
								</tr>
							</thead>
							<tbody>
								@foreach($row['competencies'] as $competency)
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
				</div>
			</div>

		@endforeach
	</div>
@endsection