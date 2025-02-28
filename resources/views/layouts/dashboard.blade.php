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
        <div class="col-md-4" >
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-2">
                            <img src="/images/{{$details['gender']}}.png" class="w-100">
                        </div>
                        <div class="col-10">
                            <b>Welcome, {{$details['fullname']}}</b><br>
                            <small class="text-secondary">{{Auth::user()->classification}}</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="card" style="height: 210px;">
                                <div class="card-body">
                                    <center>
                                        <h3><i class="fa fa-calendar text-primary"></i></h3>
                                        <small class="text-muted">Academic Year</small>
                                        <label class="">
                                            @if($academic_year)
                                                {{$academic_year->from}}-
                                                {{$academic_year->to}}
                                            @else
                                            None
                                            @endif
                                        </label>
                                    </center>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card" style="height: 210px;">
                                <div class="card-body">
                                    <center>
                                        <h3><i class="fa fa-map-pin text-primary"></i></h3>
                                        @if ($details['level'] == "Division")
                                            <small class="text-muted"> Area</small><br>
                                            <label for="">{{$details['division']}}</label>
                                        @endif
                                        @if ($details['level'] == "District")
                                            <small class="text-muted">{{$details['division']}}</small><br>
                                            <label for="">{{$details['district']}}</label>
                                        @endif
                                        @if ($details['level'] == "School")
                                            <small class="text-muted">{{$details['district']}}, {{$details['division']}}</small><br>
                                            <label for="">{{$details['school']}}</label>
                                        @endif
                                    </center>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <center><a href="/account" class="btn btn-primary"> Account</a></center>
                </div>
            </div>
        </div>
        <div class="col-md-8" >
            @if (Auth::user()->classification != "Student")
                <div class="card">
                    <div class="card-header">
                        <b>Downloadable Templates</b>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            @foreach ($templates as $code => $template)
                               
                                
                                @if ($code == "SHU"&& Auth::user()->classification == "School Head")
                                    @continue
                                @endif

                                @if ($code == "CU"&& Auth::user()->classification == "School Head")
                                    @continue
                                @endif

                                @if ($code == "ANS-KEY" && Auth::user()->classification == "Teacher")
                                    <tr>
                                        <td>
                                            <i class="fa fa-file-o"></i> {{$template}}
                                            <a href="/download_template/{{$code}}" class="text-success float-right">
                                                <i class="fa fa-arrow-down"></i> <small></small>
                                            </a>
                                        </td>
                                    </tr>
                                @endif

                                @if ( Auth::user()->classification != "Teacher")
                                    <tr>
                                        <td>
                                            <i class="fa fa-file-o"></i> {{$template}}
                                            <a href="/download_template/{{$code}}" class="text-success float-right">
                                                <i class="fa fa-arrow-down"></i> <small></small>
                                            </a>
                                        </td>
                                    </tr>
                                @endif

                            @endforeach
                        </table>

                        {{-- <div class="row">
                            @foreach ($templates as $code => $template)
                                <div class="col-md-3  mb-2">
                                    <a href="/download_template/{{$code}}">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <center>
                                                    <h5><i class="fa fa-file text-success"></i></h5>
                                                    <small class="text-secondary">
                                                        {{$template}}
                                                    </small>
                                                </center>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div> --}}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection