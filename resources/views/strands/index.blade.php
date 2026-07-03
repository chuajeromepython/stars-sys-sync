@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        var tracks = @json($tracks);
    </script>
    <script type="text/javascript" src="/js/strands.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    @include('strands.create')
    @include('strands.edit')
    @include('strands.destroy')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#create_modal"><i class="fa fa-plus mr-2"></i> Add Strand</a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_strands"  style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Track</th>
                                <th>Strand</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($strands as $strand)
                                <tr>
                                    <td>{{$strand->track}}</td>
                                    <td>{{$strand->strand}}</td>
                                    <th style="width:30%">
                                        <center>
                                        <a href="#" class="btn-edit btn btn-primary btn-sm" 
                                            data-toggle="modal" data-target="#edit_modal"
                                            data-edit_id="{{$strand->id}}"  
                                            data-edit_track_id="{{$strand->track_id}}"  
                                            data-edit_name="{{$strand->strand}}">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                            data-toggle="modal" data-target="#destroy_modal"
                                            data-destroy_id="{{$strand->id}}" 
                                            data-destroy_name="{{$strand->strand}}">
                                            <i class="fa fa-trash"></i>
                                        </a>
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