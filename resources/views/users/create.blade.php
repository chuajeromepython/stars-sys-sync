@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        var options = @json($options);
    </script>
    <script type="text/javascript" src="/js/users/create.js"></script>
    <style type="text/css">
        .select2-selection__choice{
            background-color: #332FD0 !important;
        }
    </style>
@endsection

@section('content')
	@include('layouts.message')
    {{-- <div class="container"> --}}
    <form method="post" action="/users/store" class="form">
        @csrf()
         <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <a href="/users" class="btn btn-danger"><i class="fa fa-angle-left mr-2"></i> Back</a>
                    </div>
                    <div class="card-body" >
                        <div class="row">
                            <div class="col-md-12">
                                <small class="text-primary text-bold">Area Information</small>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Classification</label>
                                <select class="select2bs4 form-control" name="classification"  required id="create_classification">
                                    <option selected disabled>-Select Classification-</option>
                                    @foreach($clasf as $clss)
                                        <option>{{$clss->classification}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3" id="div_division" style="display:none;">
                                <label class="text-muted">Division</label>
                                <select class="select2bs4 form-control" name="division" id="division">
                                   
                                </select>
                            </div>
                            <div class="col-md-3" id="div_subject" style="display:none;">
                                <label class="text-muted">Subject</label>
                                <select class="select2bs4 form-control" name="subjects[]" id="subject">
                                   
                                </select>
                            </div>
                            <div class="col-md-3" id="div_district" style="display:none;">
                                <label class="text-muted">District</label>
                                <select class="select2bs4 form-control" name="district" id="district">
                                   
                                </select>
                            </div>
                            @if(Auth::user()->classification == "School Head")
                            <div class="col-md-6">
                                <label class="text-muted">School</label>
                                <input type="hidden" name="school_id" value="{{$options['schools'][0]['id']}}">
                                <input type="text" name="" class="form-control" readonly value="{{$options['schools'][0]['name']}}">
                            </div>
                            @else
                            <div class="col-md-3" id="div_school" style="display:none;">
                                <label class="text-muted">School</label>
                                <select class="select2bs4 form-control" name="school_id" id="school">
                                   
                                </select>
                            </div>
                            @endif

                            <div class="col-md-12  mt-3">
                                <small class="text-primary text-bold">Account Information</small>
                            </div>
                            <div class="col-md-12">
                                <label class="text-muted">Username / Email</label>
                                <input type="text" class="form-control" name="username">
                            </div>
                            <div class="col-md-12 mt-3">
                                <small class="text-primary text-bold">Personal Information</small>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">First Name</label>
                                <input type="text" class="form-control" name="first_name">
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name">
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Last Name</label>
                                <input type="text" class="form-control" name="last_name">
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Suffix</label>
                                <input type="text" class="form-control" name="suffix">
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted">Birthdate</label>
                                <input type="date" class="form-control" name="birth_date">
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted">Sex</label>
                                <select class="select2bs4 form-control" name="gender">
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <center>
                            <a href="/users/create" class="btn btn-danger"><i class="fa fa-times mr-1"></i>Cancel</a>
                            <button type="submit" class="btn btn-primary btn-submit"><i class="fa fa-check mr-1"></i>Save</button>
                        </center>
                    </div>
                </div>
            </div>
        </div>
        
    </form>
    {{-- </div> --}}
@endsection