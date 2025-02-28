@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
<script type="text/javascript">
   	$(function() {
        $('#dt_surveys').dataTable({
            'language':{
                'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
            }
        });
    });
</script>
<style type="text/css">
    .card-icon{
        font-size: 35px;
    }
</style>
@endsection
    
@section('content')
@include('layouts.message')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outlined-primary">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 col-md-1">
                            <img src="/images/{{$details['gender']}}.png" class="w-100">
                        </div>
                        <div class="col-md-8 col-8">
                            <a href="/users/{{Auth::user()->id}}" target="_blank">
                            <span style="font-size: 1.2em;" class="text-bold text-dark">Welcome, {{$details['fullname']}}</span><br></a>
                            <span class="text-bold text-primary">{{$details['classification']}}</span><br>
                            <span>
                                <i  class="fa fa-map-pin"></i>
                                {{ ($details['division']) ? $details['division'] : "" }}
                                {{ ($details['district']) ? " | ".$details['district'] : "" }}
                                {{ ($details['school']) ? " | ".$details['school'] : "" }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card">
                <div class="card-body text-primary">
                    <center>
                        <i class="fa fa-calendar card-icon"></i>
                        <br><small class="text-muted">Active Academic Year</small>
                        <h4 class="">
                            @if($academic_year)
                                {{$academic_year->from}}-
                                {{$academic_year->to}}
                            @else
                               None
                            @endif
                        </h4>
                    </center>
                </div>
            </div>
        </div>
        @if(
            Auth::user()->classification == "System Administrator" || 
            Auth::user()->classification == "Division Administrator" || 
            Auth::user()->classification == "Division Supervisor" || 
            Auth::user()->classification == "District Supervisor" ||
            Auth::user()->classification == "Division Superintendent" ||
            Auth::user()->classification == "Assistant Division Superintendent" || 
            Auth::user()->classification == "Chief of CID" || 
            Auth::user()->classification == "Chief of SGOD"

        )
        <div class="col-md-3 col-6">
            <div class="card">
                <div class="card-body text-purple">
                    <center>
                        <i class="fa fa-users card-icon"></i>
                        <br><small class="text-muted">Users</small>
                        <h4 class="">
                            {{$users}}
                        </h4>
                    </center>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card">
                <div class="card-body text-info">
                    <center>
                        <i class="fa fa-building card-icon"></i>
                        <br><small class="text-muted">Schools</small>
                        <h4 class="">
                            {{$schools}}
                        </h4>
                    </center>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card">
                <div class="card-body text-warning">
                    <center>
                        <i class="fa fa-building card-icon"></i>
                        <br><small class="text-muted">Students</small>
                        <h4 class="">
                            {{$students}}
                        </h4>
                    </center>
                </div>
            </div>
        </div>
        @endif
        
        @if(Auth::user()->classification != "Student")
        <div class="col-md-3 col-6">
            <a data-toggle="modal" data-target="#uploader_modal">
                <div class="card">
                    <div class="card-body text-success">
                        <center>
                            <i class="fa fa-file card-icon"></i>
                            <br><small class="text-muted">Download Templates</small>
                            <h4 class="">
                                Uploaders
                            </h4>
                        </center>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>
    
    <div class="modal fade" role="dialog"  id="uploader_modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-download"></i> Downloadable Uploader Templates</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-bordered table-striped">
                        @foreach ($templates as $code => $template)
                            <tr>
                                <td>
                                    {{$template}}

                                    <a href="/download_template/{{$code}}" class="btn btn-success btn-sm  float-right">
                                        <i class="fa fa-download"></i>
                                    </a>
                                
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection