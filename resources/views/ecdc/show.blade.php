@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
	<script>
        var screen_width = $(window).width();
        var screen_height = $(window).height();
        var classroom_id = {{$classroom->id}};
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $('#dt_ecdcs').dataTable({
                'language':{
                    'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
                },
                'scrollX': (screen_height > screen_width) ? true : false
            });
            
            $('#dt_students').dataTable({
                'language':{
                    'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
                },
                'scrollX': (screen_height > screen_width) ? true : false
            });
            
            $('#dt_student_lists').dataTable({
                'language':{
                    'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
                },
                'scrollX': (screen_height > screen_width) ? true : false
            });    


            $(".btn-show-list").click(function() {
                appendLoader();
                $('#modal_student_list').modal('show');
                setTimeout(() => {
                    $("#row_loader").hide();
                }, 1000);
                getECDCResult($(this).data("ecdc_id"));
                
            })
            $('#modal_student_list').on('hidden.bs.modal', function () {
                appendLoader();
            })

            $('#dt_ecdcs tbody').on( 'click', '.btn-edit', function () {
        
                var id = $(this).data('id');
                var period = $(this).data('period');
                var date = $(this).data('date');
                console.log("hahaha")
                $('#edit_id').val(id);
                $('#edit_period').val(period).change();
                $('#edit_date').val(date);
            });
        });

        function getECDCResult(ecdc_id){
            $.ajax({
                url: '/getECDCResult',
                type: "POST",
                data: {
                    "ecdc_id" : ecdc_id
                },
                success: function(data){

                    $('#dt_student_lists').dataTable().fnClearTable();
                    $('#dt_student_lists').dataTable().fnDestroy();
                    
                    var html = "";
                    $.each(data, function(student_id, student) {

                        var link = "/ecdcs/classroom/"+classroom_id+"/card/"+student_id;
                        var row = "<tr>"
                            row+= "<td>"+student.lrn+"</td>"
                            row+= "<td>"+student.name+"</td>"
                            row+= "<td>"+student.age+"</td>"
                            row+= "<td>"+student.total_scaled_score+"</td>"
                            row+= "<td>"+student.standard_score+"</td>"
                            row+= "<td>"+student.interpretation+"</td>"
                            row+= "<td><a href='"+link+"' class='btn btn-primary btn-sm'>VIEW</a></td>"
                            row+="</tr>";
                        html+=row
                        
                    });

                    $("#tbody_student_list").html(html);

                    $('#dt_student_lists').dataTable({
                        'language':{
                            'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
                        },
                        'scrollX': (screen_height > screen_width) ? true : false
                    });    
                },
            });
        }

        function appendLoader(){
            var row = "<tr id='row_loader'>"
                row += "<td colspan='7' ><center><span class='badge bg-danger'>PROCESSING ...</span></center></td>"
                row += "</tr>"
            $("#tbody_student_list").html(row); 
        }

    </script>
@endsection

@section('content')

	@include('layouts.message')
	@include('ecdc.upload')
	@include('ecdc.edit')

    <div class="row">
        <div class="col-md-12 mb-2 ">
            <a href="/ecdcs" class="btn btn-danger">
                <i class="fa fa-angle-left mr-2"></i> Back
            </a>
        </div>
        <div class="col-md-12 mb-2">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8 col-12 mb-2">
                            <small class=""><b>LEGEND</b></small><br>
                            <span class="badge bg-teal">Beginning Of S.Y.</span>
                            <span class="badge bg-info">Mid Of S.Y.</span>
                            <span class="badge bg-purple">End Of S.Y.</span>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a class="btn-block btn btn-info" data-toggle="modal" data-target="#upload_modal">
                                <i class="fa fa-upload"></i>
                                Upload
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a class="btn-block btn btn-success" href="/ecdcs/{{$classroom->id}}/download_template">
                                <i class="fa fa-download"></i>
                                Download Template
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="dt_ecdcs">
                        <thead>
                            <tr>
                                <th colspan="5" class="text-primary">
                                    <center>
                                        List of Uploaded ECDC
                                    </center>
                                </th>
                            </tr>
                            <tr>
                                <th>Date Uploaded</th>
                                <th>Date of Assessment</th>
                                <th>Section</th>
                                <th>Period</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ecdcs as $ecdc)
                                <tr>
                                    <td>{{$ecdc->created_at}}</td>
                                    <td>{{$ecdc->date}}</td>
                                    <td>{{$ecdc->section}}</td>
                                    <td>
                                        <center>
                                        @if ($ecdc->period == 1)
                                            <span class="badge bg-teal">BOSY</span>
                                        @elseif ($ecdc->period == 2)
                                            <span class="badge bg-info">MOSY</span>
                                        @else
                                            <span class="badge bg-purple">EOSY</span>
                                        @endif
                                        </center>
                                    </td>
                                    <td>
                                        <center>
                                            <a href="#" class="btn btn-info btn-sm btn-edit mb-1"
                                            data-id="{{$ecdc->id}}"
                                            data-period="{{$ecdc->period}}"
                                            data-date="{{$ecdc->date}}"
                                            data-toggle="modal" data-target="#edit_modal"
                                            style="width: 60px;">
                                                <i class="fa fa-pen"></i>
                                            </a>
                                            <a href="#" class="btn btn-primary btn-sm btn-show-list mb-1"
                                            data-ecdc_id="{{$ecdc->id}}" style="width: 60px;">
                                                <i class="fa fa-users"></i>
                                            </a>
                                        </center>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a class="btn btn-primary" href="/ecdcs/classroom/{{$classroom->id}}/create">
                        <i class="fa fa-plus"></i>
                        Encode ECDC
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-bordered table-hover" id="dt_students">
                        <thead>
                            <th colspan="6" class="text-primary">
                                <center>
                                    Student ECD Checklist
                                </center>
                            </th>
                            <tr>
                                <th>LRN</th>
                                <th>Name</th>
                                <th>BoSY</th>
                                <th>MoSY</th>
                                <th>EoSY</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $lrn => $student)
                                <tr>
                                    <td>{{$lrn}}</td>
                                    <td>{{$student['name']}}</td>
                                    <td>
                                        <center>
                                            {!! ($student["has_bosy"] == 1) 
                                                ? '<i class="fa fa-check text-success"></i>' 
                                                : '<i class="fa fa-times text-danger"></i>'
                                            !!}
                                        </center>
                                    </td>
                                    <td>
                                        <center>
                                            {!! ($student["has_mosy"] == 1) 
                                                ? '<i class="fa fa-check text-success"></i>' 
                                                : '<i class="fa fa-times text-danger"></i>'
                                            !!}
                                        </center>
                                    </td>
                                    <td>
                                        <center>
                                            {!! ($student["has_eosy"] == 1) 
                                                ? '<i class="fa fa-check text-success"></i>' 
                                                : '<i class="fa fa-times text-danger"></i>'
                                            !!}
                                        </center>
                                    </td>
                                    <td>
                                        <center>
                                            <a href="/ecdcs/classroom/{{$classroom->id}}/card/{{$student['id']}}" class="btn btn-primary btn-sm">
                                                <i class="fa fa-folder"></i> &nbsp; VIEW
                                            </a>
                                        </center>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                    <table class="table table-sm table-striped table-bordered" id="dt_student_lists">
                        <thead>
                            <tr>
                                <th>LRN</th>
                                <th style="width: 30%;">Name</th>
                                <th style="width: 10%;">Age</th>
                                <th style="width: 10%;">Total Scaled Score</th>
                                <th style="width: 10%;">Standard Score</th>
                                <th style="width: 40%;">Interpretation</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tbody_student_list">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection