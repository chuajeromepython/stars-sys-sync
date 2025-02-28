@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
@endsection

@section('content')
	@include('layouts.message')
    <div class="col-12 col-sm-12">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="ecd-tab" role="tablist">
                    <li class="nav-item">
                    <a class="nav-link" id="ecd-summary-tab" data-toggle="pill" href="#custom-tabs-four-home" 
                        role="tab" aria-controls="custom-tabs-four-home" aria-selected="true">ECD Summary</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link active" id="ecd-raw-tab" data-toggle="pill" href="#ecd-raw" 
                        role="tab" aria-controls="ecd-raw" aria-selected="false">Competencies</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-four-messages-tab" data-toggle="pill" href="#custom-tabs-four-messages" 
                        role="tab" aria-controls="custom-tabs-four-messages" aria-selected="false">Card</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-four-settings-tab" data-toggle="pill" href="#custom-tabs-four-settings" role="tab" aria-controls="custom-tabs-four-settings" aria-selected="false">Settings</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="ecd-tabContent">
                    <div class="tab-pane fade" id="custom-tabs-four-home" role="tabpanel" aria-labelledby="ecd-summary-tab">
                        @include('ecdc.card_tabs.ecd_summary')
                    </div>
                    <div class="tab-pane fade active show" id="ecd-raw" role="tabpanel" aria-labelledby="ecd-raw-tab">
                        @include('ecdc.card_tabs.ecd_competency')
                    </div>
                    <div class="tab-pane fade" id="custom-tabs-four-messages" role="tabpanel" aria-labelledby="custom-tabs-four-messages-tab">
                        <a href="/ecdcs/classroom/{{$classroom->id}}/card/{{$student->id}}/print"
                            class="btn btn-primary mb-3" target="blank">
                            PRINT
                        </a>
                        <iframe src="/ecdcs/classroom/{{$classroom->id}}/card/{{$student->id}}/print" 
                            frameborder="0" style="width: 100%; height: 500px"></iframe>
                    </div>
                    <div class="tab-pane fade" id="custom-tabs-four-settings" role="tabpanel" aria-labelledby="custom-tabs-four-settings-tab">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog"  id="modal_student_list">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <b class="modal-title text-primary"><i class="fa fa-list"></i> Student List</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="overflow: auto">
                    
                </div>
            </div>
        </div>
    </div>
@endsection