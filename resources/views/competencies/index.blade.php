@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/competencies.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('competencies.upload')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="/competencies/create" class="btn btn-primary"><i class="fa fa-plus mr-2"></i> Add Competencies</a>
                    <a href="#" class="btn btn-info"
                        data-toggle="modal" 
                        data-target="#upload_modal_competencies">
                        <i class="fa fa-upload mr-2"></i> Upload Competencies
                    </a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_competencies">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Code</th>
                                <th>Description</th>
                                <th style="width: 15%;">Subject</th>
                                <th style="width: 10%;">Grade Level</th>
                                <th style="width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($competencies as $competency)
                                <tr>
                                    <td>{{$competency->code}}</td>
                                    <td>{{$competency->description}}</td>
                                    <td>{{$competency->title}}</td>
                                    <td>{{$competency->level}}</td>
                                    <th>
                                        <center>
                                         <a href="/competencies/{{$competency->id}}/edit" class="btn-primary btn-sm btn"><i class="fa fa-pen"></i></a>
                                        <a href="/subjects//destroy" class="btn-danger btn-sm btn"><i class="fa fa-trash"></i></a>
                                       </center>
                                    </th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection