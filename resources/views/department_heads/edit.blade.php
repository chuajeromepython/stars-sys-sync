@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
@endsection

@section('content')
    @include('layouts.message')
    <form method="post" action="/department_heads/update" class="form">
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
                                <input type="hidden" name="id" value="{{$department_head->id}}">
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted">School</label>
                                @if(Auth::user()->classification == 'School Head')
                                    <input type="text" class="form-control" disabled value="{{$schools[0]->name}}">
                                @else
                                    <select class="select2bs4 form-control" name="school_id">
                                        @foreach($schools as $school)
                                            <option value="{{$school->id}}"
                                                {{ ($school->id == $department_head->school_id) ? 'selected' : ''}}
                                                > {{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted">Subjects</label>
                                <select class="select2bs4 form-control" name="subjects[]" id="subject" multiple="multiple">
                                   @foreach($subjects as $subject)
                                        <option value="{{$subject->id}}" 
                                            {{ in_array($subject->id, $current_subjects) ? 'selected' : ''}}>
                                            {{$subject->title}}
                                        </option>
                                   @endforeach
                                </select>
                            </div>
                          
                           
                            <div class="col-md-12  mt-3">
                                <small class="text-primary text-bold">Account Information</small>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted">Username</label>
                                <input type="text" class="form-control" name="username" disabled 
                                value="{{$user->username}}">
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted">Email</label>
                                <input type="text" class="form-control" name="email" 
                                value="{{$department_head->email}}">
                            </div>
                            <div class="col-md-12 mt-3">
                                <small class="text-primary text-bold">Personal Information</small>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">First Name</label>
                                <input type="text" class="form-control" name="first_name"
                                value="{{$person->first_name}}">
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name"
                                value="{{$person->middle_name}}">
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Last Name</label>
                                <input type="text" class="form-control" name="last_name"
                                value="{{$person->last_name}}">
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted">Suffix</label>
                                <input type="text" class="form-control" name="suffix"
                                value="{{$person->suffix}}">
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted">Birthdate</label>
                                <input type="date" class="form-control" name="birth_date"
                                value="{{$person->birth_date}}">
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted">Sex</label>
                                <select class="select2bs4 form-control" name="gender">
                                    <option value="M" {{($person->gender == "M")?'selected' : ''}}>Male</option>
                                    <option value="F" {{($person->gender == "F")?'selected' : ''}}>Female</option>
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
@endsection