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
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-gradient-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img src="/images/{{$details['gender']}}.png" class="rounded-circle" style="width: 60px; height: 60px;">
                        </div>
                        <div class="col">
                            <h4 class="mb-0 text-white">Welcome, {{$details['fullname']}}</h4>
                            <p class="mb-0 text-white-50">
                                <i class="fa fa-user-tag mr-1"></i>{{Auth::user()->classification}}
                                @if ($details['level'] == "School" && $details['school'])
                                    <span class="mx-2">|</span> <i class="fa fa-school mr-1"></i>{{$details['school']}}
                                @endif
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="text-right text-white">
                                <small><i class="fa fa-calendar mr-1"></i>Academic Year</small>
                                <h5 class="mb-0">
                                    @if($academic_year)
                                        {{$academic_year->from}}-{{$academic_year->to}}
                                    @else
                                        Not Set
                                    @endif
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="mb-3">
        <h5 class="mb-3"><i class="fa fa-chart-line mr-2"></i>Statistics</h5>
        <div class="d-flex flex-wrap gap-3">
            @foreach($statistics as $stat)
                <div class="flex-stat-card">
                    <a href="{{$stat['link']}}" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">{{$stat['title']}}</h6>
                                        <h2 class="mb-0">{{$stat['value']}}</h2>
                                    </div>
                                    <div>
                                        <div class="bg-{{$stat['color']}} rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa {{$stat['icon']}} fa-2x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Reports Section -->
    @if(count($reports) > 0)
    <div class="mb-3">
        <h5 class="mb-3"><i class="fa fa-file-download mr-2"></i>Available Reports</h5>
        <div class="d-flex flex-wrap gap-3">
            @foreach($reports as $report)
                <div class="flex-report-card">
                    <a href="{{$report['url']}}" class="text-decoration-none">
                        <div class="card border-left-info h-100 hover-shadow">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="bg-info rounded p-2 mr-2">
                                        <i class="fa {{$report['icon']}} text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 font-weight-bold">{{$report['title']}}</h6>
                                    </div>
                                </div>
                                <small class="text-muted d-block mb-2">{{$report['description']}}</small>
                                <div class="text-right">
                                    <small class="text-info"><i class="fa fa-download mr-1"></i>Download</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Quick Links Section -->
        <div class="col-md-{{ Auth::user()->classification != 'Student' ? '6' : '12' }}">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-bolt mr-2"></i>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($quickLinks as $link)
                            <div class="col-md-6 mb-3">
                                <a href="{{$link['url']}}" class="text-decoration-none">
                                    <div class="card border-left-{{$link['color']}} h-100 hover-shadow">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-{{$link['color']}} rounded p-2 mr-3">
                                                    <i class="fa {{$link['icon']}} fa-lg text-white"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{$link['title']}}</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-{{ Auth::user()->classification != 'Student' ? '6' : '12' }}">
            @if (Auth::user()->classification != "Student")
                <!-- Templates & Resources -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fa fa-file-download mr-2"></i>Downloadable Templates</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <div class="list-group list-group-flush">
                            @foreach ($templates as $code => $template)
                                @if ($code == "SHU" && Auth::user()->classification == "School Head")
                                    @continue
                                @endif

                                @if ($code == "CU" && Auth::user()->classification == "School Head")
                                    @continue
                                @endif

                                @if ($code == "ANS-KEY" && Auth::user()->classification == "Teacher")
                                    <a href="/download_template/{{$code}}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa fa-file-excel text-success mr-2"></i>
                                            <span>{{$template}}</span>
                                        </div>
                                        <i class="fa fa-download text-success"></i>
                                    </a>
                                @endif

                                @if (Auth::user()->classification != "Teacher")
                                    <a href="/download_template/{{$code}}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa fa-file-excel text-success mr-2"></i>
                                            <span>{{$template}}</span>
                                        </div>
                                        <i class="fa fa-download text-success"></i>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <!-- Student Info Card -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fa fa-info-circle mr-2"></i>Student Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted">School</small>
                                <p class="mb-0 font-weight-bold">{{$details['school'] ?? 'N/A'}}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted">District</small>
                                <p class="mb-0 font-weight-bold">{{$details['district'] ?? 'N/A'}}</p>
                            </div>
                            <div class="col-md-12 mb-3">
                                <small class="text-muted">Division</small>
                                <p class="mb-0 font-weight-bold">{{$details['division'] ?? 'N/A'}}</p>
                            </div>
                        </div>
                        <a href="/account" class="btn btn-info btn-block">
                            <i class="fa fa-user mr-2"></i>View My Profile
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    .border-left-primary { border-left: 4px solid #007bff; }
    .border-left-success { border-left: 4px solid #28a745; }
    .border-left-info { border-left: 4px solid #17a2b8; }
    .border-left-warning { border-left: 4px solid #ffc107; }
    .bg-gradient-primary {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
    }
    
    /* Flexbox layout for statistics and reports */
    .d-flex.flex-wrap {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 1rem !important;
    }
    
    .flex-stat-card {
        flex: 0 0 auto;
        width: calc(20% - 1rem);
        min-width: 220px;
    }
    
    .flex-report-card {
        flex: 0 0 auto;
        width: calc(20% - 1rem);
        min-width: 220px;
    }
    
    @media (max-width: 1400px) {
        .flex-stat-card, .flex-report-card {
            width: calc(25% - 1rem);
        }
    }
    
    @media (max-width: 1200px) {
        .flex-stat-card, .flex-report-card {
            width: calc(33.333% - 1rem);
        }
    }
    
    @media (max-width: 768px) {
        .flex-stat-card, .flex-report-card {
            width: calc(50% - 1rem);
        }
    }
    
    @media (max-width: 576px) {
        .flex-stat-card, .flex-report-card {
            width: 100%;
        }
    }
    
    .gap-3 {
        gap: 1rem !important;
    }
</style>
@endsection