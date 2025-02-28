@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/item_banks.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <label class="text-primary">Generate Items</label>
                </div>
                <div class="card-body" >
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-bold">Academic Year</small>
                            <select class="select2bs4 form-control" name="academic_year_id" id="academic_year_id">
                                <option value="0">-Select Academic Year-</option>
                                @foreach($academic_years as $academic_year)
                                    <option value="{{$academic_year->id}}">{{$academic_year->from}} - {{$academic_year->to}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <small class="text-bold">Grade Level</small>
                            <select class="select2bs4 form-control" name="grade_level_id" id="grade_level_id">
                                
                            </select>
                        </div>
                         <div class="col-md-3">
                            <small class="text-bold">Subject</small>
                            <select class="select2bs4 form-control" name="subject_id" id="subject_id">
                                
                            </select>
                        </div>
                        <div class="col-md-3">
                            <small>&nbsp;</small>
                            <a href="#" id="btn_generate" class="btn btn-primary btn-block">Generate</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <label class="text-primary">Item References</label>
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="dt_items">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Competency Code</th>
                                <th>Question</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody id="item_body">
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection