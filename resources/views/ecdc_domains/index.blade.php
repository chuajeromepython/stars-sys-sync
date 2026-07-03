@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
@endsection

@section('content')
	@include('layouts.message')

	<div class="container">
		<div class="card">
			<div class="card-header">
				<label class="text-primary">ECDC Domains</label>
			</div>
			<div class="card-body">
				<div id="accordion">

					@foreach($domains as $domain)
						<div class="card">
				            <div class="card-header bg-{{$colors[$domain->id-1]}}">
					            <h4 class="card-title w-100 ">
					                <a class="d-block w-100 text-white" data-toggle="collapse" href="#domain-{{$domain->id}}">
					                  	{{$domain->domain}}
					                </a>
					            </h4>
				            </div>
				            <div id="domain-{{$domain->id}}" class="collapse" data-parent="#accordion">
					            <div class="card-body">
					            	<ol>
					                @foreach($competencies[$domain->id] as $competency)
				                		<li class="text-{{$colors[$domain->id-1]}}">
				                			<span class="text-normal text-dark">{{$competency->competency}}</span>
				                		</li>
					                @endforeach
					                </ol>
					            </div>
				            </div>
			          	</div>
					@endforeach
		            
		        </div>
			</div>
		</div>
	</div>
    
@endsection