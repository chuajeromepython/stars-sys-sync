@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        $(function() {
            $("#lrn").focusout(function () {
                if ($(this).val().length != 12){
                    alert("LRN must be 12 digits!");
                }
            });
        });
    </script>
@endsection

@section('content')
	@include('layouts.message')
    {{-- <div class="container"> --}}
    <form method="post" action="/students/store" class="form">
        @csrf()
         <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <a href="/dashboard" class="btn btn-danger"><i class="fa fa-angle-left mr-2"></i> Back</a>
                    </div>
                    <div class="card-body" >
                        <div class="row">
                            <div class="col-md-12">
                                <small class="text-primary text-bold">Area Information</small>
                            </div>
                            <div class="col-md-12">
                                <label class="text-muted">School</label>
                                <input type="text" name="" class="form-control" disabled value="{{$school->code}} - {{$school->name}}">
                                <input type="hidden" class="" name="school_id" value="{{$school->id}}">
                            </div>
                             <div class="col-md-12  mt-3">
                                <small class="text-primary text-bold">Account Information</small>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted">LRN</label>
                                <input type="number" id="lrn" class="form-control" name="lrn" 
                                placeholder="Ex: 100000000001" required>
                            </div>
                            <div class="col-md-12 mt-3">
                                
                                <small class="text-primary text-bold">Personal Information</small>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">First Name</label>
                                <input type="text" class="form-control" name="first_name" 
                                placeholder="Type first name here...">
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" 
                                placeholder="Type middle name here...">
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Last Name</label>
                                <input type="text" class="form-control" name="last_name" 
                                placeholder="Type last name here...">
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Suffix</label>
                                <input type="text" class="form-control" name="suffix"
                                placeholder="Type suffix here...">
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
                            <a class="btn btn-danger btn-cancel"><i class="fa fa-times mr-1"></i>Cancel</a>
                            <button type="submit" class="btn btn-primary btn-submit"><i class="fa fa-check mr-1"></i>Save</button>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- </div> --}}
@endsection