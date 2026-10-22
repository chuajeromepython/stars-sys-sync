@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/teachers.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    {{-- @include('teachers.destroy') --}}
    @include('teachers.upload')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    @can('teachers.manage')
                    <a href="/teachers/create" class="btn btn-primary" ><i class="fa fa-plus mr-2"></i> Add Teacher</a>
                    @endcan
                    @can('teachers.upload')
                    <a href="#" class="btn btn-info" 
                        data-toggle="modal" 
                        data-target="#upload_modal_teachers">
                        <i class="fa fa-upload mr-2"></i> 
                        Upload Teacher
                    </a>
                    @endcan
                </div>
                <div class="card-body" >
                    @if (in_array($hierarchy['scope'], ['system', 'division', 'district']))
                        <div class="row mb-3">
                            @if (in_array($hierarchy['scope'], ['system', 'division']))
                                <div class="col-md-4">
                                    <label for="teacher-district">District</label>
                                    <select class="form-control" id="teacher-district" name="district_id">
                                        <option value="">All districts</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-4">
                                <label for="teacher-school">School</label>
                                <select class="form-control" id="teacher-school" name="school_id">
                                    <option value="">All schools</option>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}" data-district="{{ $school->district_id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    <table class="table table-bordered mb-3" id="dt_teachers">
                        <thead>
                            <tr>
                                <th>Username / Email</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
