@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
	<script type="text/javascript">
		$(function() {
			$(document).on('change', '#classroom',function(){
				id = $(this).val();
				$('.btn-download-ecdc').attr('href', '/ecdcs/'+id+'/download_template');
				$('.btn-download-ecdc').removeClass('disabled');
			});

			var screen_width = $(window).width();
			var screen_height = $(window).height();

			$('#dt_ecdc').dataTable({
				'language':{
					'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
				},
				'scrollX': (screen_height > screen_width) ? true : false
			});

			$('#dt_ecdc tbody').on( 'click', '.btn-edit', function () {
        
				var id = $(this).data('id');
				var period = $(this).data('period');
				var date = $(this).data('date');

				$('#edit_id').val(id);
				$('#edit_period').val(period).change();
				$('#edit_date').val(date);
			});

		});
	</script>
@endsection

@section('content')
    <div class="container">
        
	    @include('layouts.message')
        <div class="row">
            <div class="col-md-12 mb-2">
                <a href="/ecdcs" class="btn btn-danger">
                    <i class="fa fa-angle-left mr-2"></i> Back
                </a>
            </div>

            <form action="/ecdcs/store" method="post">
                @csrf
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <span for="" class="text-primary text-bold">Encode New ECDC</span>
                        </div>
                        <div class="card-header">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <small  class="text-bold text-primary">Student</small>
                                    <select class="select2bs4 form-control" name="student_id" id="student_id" required>
                                        <option selected disabled>-- Select Student --</option>
                                        @foreach ($students as $student)
                                            <option value="{{$student->student_id}}">
                                                {{$student->lrn}} - {{$student->last_name}}, {{$student->first_name}}
                                                {{$student->middle_name}} 
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <small  class="text-bold text-primary">Period</small>
                                    <select class="select2bs4 form-control" name="period" id="period" required>
                                        <option selected disabled>-- Select Period --</option>
                                        <option value="1">BoSY - Beginning of School Year</option>
                                        <option value="2">MoSY - Mid of School Year</option>
                                        <option value="3">EoSY - End of School Year</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <small  class="text-bold text-primary">Date of Assessment</small>
                                    <input type="date" class="form-control" name="date">
                                    <input type="hidden" class="form-control" name="classroom_id" value="{{$classroom->id}}">
                                </div>
                            </div>
                            @foreach ($domains as $id => $domain)
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th colspan="4" class="bg-{{$domain['color']}} text-white">
                                                <center>{{$domain['domain']}}</center>
                                            </th>
                                        </tr>
                                        <tr  class="text-{{$domain['color']}}""> 
                                            <th style="width: 80%">Competencies</th>
                                            <th style="width: 10%">Kayang Gawin</th>
                                            <th style="width: 10%">Di pa kayang gawin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($domain['competencies'] as $competency)
                                            <tr>
                                                <td>{{$competency->id}}. {{$competency->competency}}</td>
                                                <td>
                                                    <center> 
                                                        <input type="radio" name="score[{{$competency->id}}]" value="1">
                                                    </center>
                                                </td>
                                                <td>
                                                    <center> 
                                                        <input type="radio" name="score[{{$competency->id}}]" value="0">
                                                    </center>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
	</div>
@endsection