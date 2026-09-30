@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
<script src="/js/trails.js"></script>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-4">
                <small class="text-bold text-danger">
                    Select Model to generate Audit Trails
                </small>
                <select name="" id="models" class="select2bs4 form-control">
                    @foreach ($models as $model)
                        <option value="{{$model}}">{{$model}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                {{-- <a href="#" id="btn_generate" class="btn btn-sm btn-danger">Generate Trails</a> --}}
            </div>
        </div>
    </div>
    <div class="card-body" style="overflow: auto">
        <table class="table mt-3" style="display:none;  word-wrap:break-word !important;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Event</th>
                    <th>User</th>
                    <th>ID</th>
                    <th>Old Values</th>
                    <th>New Values</th>
                </tr>
            </thead>
            <tbody id="tbody">
                
            </tbody>
        </table>
    </div>
</div>

@endsection