@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
        var school_id = {{$school_id}};
    </script>
    <script type="text/javascript" src="/js/classrooms.js"></script>
    <style type="text/css">
        .section-icon{
            font-size: 30px;
            height: 50px;
            width: 50px;
            line-height: 50px;
            border-radius: 50%;
        }

        .spinner{
            animation-name: spin;
            animation-duration: 2000ms;
            animation-iteration-count: infinite;
            animation-timing-function: linear; 
        }

        @keyframes spin {
            from {
                transform:rotate(0deg);
            }
            to {
                transform:rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
	@include('layouts.message')
    @include('classrooms.upload')
    @include('classrooms.edit')
    @include('classrooms.destroy')
    <div class="container">
        
        @if(Auth::user()->classification == "School Head")
        <a href="/classrooms/create" class="btn btn-primary mr-2"><i class="fa fa-plus mr-2"></i> Add Classroom</a>
        <a href="#" class="btn btn-info" 
            data-toggle="modal" 
             data-target="#upload_modal_advisory"><i class="fa fa-upload mr-2"></i> Upload Advisory Class</a>
        <br><br>
        @endif

        @if(sizeof($classrooms) == 0)
            <div class="callout callout-info">
                <h5><i class="fa fa-bullhorn mr-2"></i> No Classroom found.</h5>
                <p>{{$message}}</p>
            </div>
        @endif
        
        @foreach($classrooms as $grade_level => $rooms)
            <div class="card ">
                <div class="card-header">
                    <label class="text-primary">{{$grade_level}}</label>
                </div>
                <div class="card-body">
                    <div class="row">
                    @foreach($rooms as $room)
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header">
                                    @if(Auth::user()->classification == "Teacher")
                                        @if($room['is_advisory'] == 1)
                                            <span class="badge badge-primary">ADVISORY</span>
                                        @else
                                            <span class="badge badge-success">
                                                SUBJECT CLASS ( {{$room['classes']}} )
                                            </span>
                                        @endif
                                    @else
                                        <a class="float-right text-muted" data-toggle="dropdown" href="#">
                                            <i class="fa fa-cog"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <span class="dropdown-item dropdown-header">Classroom Settings</span>
                                            {{-- Edit --}}
                                            <div class="dropdown-divider"></div>
                                                <a href="#"
                                                data-classroom_id="{{$room['classroom_id']}}" 
                                                data-section="{{$room['section']}}"
                                                data-advisor="{{$room['advisor']}}" 
                                                class="dropdown-item btn-edit" data-toggle="modal" data-target="#edit_modal">
                                                <i class="fas fa-pen mr-2"></i> Edit
                                                </a>
                                            {{-- Delete --}}
                                            <div class="dropdown-divider"></div>
                                                <a href="#" 
                                                data-classroom_id="{{$room['classroom_id']}}" 
                                                data-section="{{$room['section']}}"
                                                data-grade_level="{{$grade_level}}"     
                                                data-toggle="modal" data-target="#destroy_modal"    
                                                class="dropdown-item btn-destroy">
                                                <i class="fas fa-trash mr-2"></i> Delete
                                                <span class="float-right badge badge-danger">
                                                    <i class="fa fa-exclamation"></i>
                                                </span>
                                                </a>
                                            <a href="#" class="dropdown-item dropdown-footer text-muted"></a>
                                        </div>
                                    @endif
                                </div>
                               <div class="card-body div-room" data-id="{{$room['classroom_id']}}">
                                    <center>
                                        <div class="section-icon bg-primary mb-2">
                                            <i class="fa fa-chair"></i>
                                        </div>
                                        <h4>{{$room['section']}}</h4>
                                        <small class="text-muted"> {{$room['advisor']}} </small> <br>
                                        <small class="text-muted">
                                            {{ ( $room['subject'] == "Edukasyong Pantahanan at Pangkabuhayan (EPP)" ) ? 'EPP' : $room['subject']}}
                                        </small>
                                    </center>
                               </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection