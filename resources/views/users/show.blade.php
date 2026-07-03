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
    @include('users.includes.edit_classification')
    <div class="container">
         <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body box-profile">
                        <div class="text-center">
                          <img class="profile-user-img img-fluid img-circle"  src="/images/deped_logo.png">
                        </div>
                        <h3 class="profile-username text-center">{{$details['fullname']}}</h3>
                        <p class="text-muted text-center">{{$details['classification']}}</p>
                        <hr>
                        <strong><i class="fa fa-calendar mr-3"></i> Birthdate</strong>
                        <p class="text-muted float-right">{{$details['birth_date']}}</p>
                        <hr>
                        <strong><i class="fa fa-user mr-3"></i> Sex</strong>
                        <p class="text-muted float-right">{{$details['gender']}}</p>
                        <hr>
                        <strong><i class="fa fa-envelope mr-3"></i> Username / Email </strong>
                        <p class="text-muted float-right">{{$user->username}}</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <center class="text-bold">
                            Area
                        </center>
                    </div>
                    <div class="card-body">
                        @if($details['division'] != null)
                            <strong><i class="fa fa-map mr-2"></i> Division</strong>
                            <p class="text-muted float-right">{{$details['division']}}</p>
                        @endif
                        @if($details['district'] != null)
                            <hr>
                            <strong><i class="fa fa-map-pin mr-3"></i> District</strong>
                            <p class="text-muted float-right">{{$details['district']}}</p>
                        @endif
                        @if($details['school'] != null)
                            <hr>
                            <strong><i class="fa fa-school mr-2 mb-2"></i> School</strong>
                             <p class="text-muted ml-4">{{$details['school']}}</p>
                        @endif
                        @if($details['subjects'] != null)
                            <hr>
                            <strong><i class="fa fa-book mr-3 mb-2"></i> Subjects</strong>
                            <ul>
                                @foreach($details['subjects'] as $subject)
                                <li class="text-muted">{{$subject->title}}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <label class="text-bold">User Classification History</label> 
                        <a href="#" class="btn btn-info btn-sm float-right"  id="btn_edit_classification" 
                            data-toggle="modal" 
                            data-target="#edit_modal"
                            data-user_id="{{$user->id}}"
                            data-classification="{{$user->classification}}">
                            Update Classification</a>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table">
                            <tr>
                                <th>Classification</th>
                                <th>Area</th>
                                <th>Date Created</th>
                                <th>Encoded By</th>
                            </tr>
                            @foreach($histories as $history)
                            <tr>
                                <td>{{$history['classification']}}</td>
                                <td>
                                    <ul>
                                    @if($history['school'] != null)
                                        <li>{{$history['school']}}</li> 
                                    @endif
                                    @if($history['district'] != null)
                                        <li>{{$history['district']}}</li>
                                    @endif
                                    @if($history['division'] != null)
                                        <li>{{$history['division']}}</li>
                                    @endif
                                    </ul>
                                </td>
                                <td>{{$history['created_at']}}</td>
                                <td>{{$history['encoder']}}</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection