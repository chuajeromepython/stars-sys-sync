@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/semesters.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="/students/create" class="btn btn-primary" ><i class="fa fa-plus mr-2"></i> Add Student</a>
                </div>
                <div class="card-body" >
                    <table class="table table-bordered mb-3" id="dt_semesters">
                        <thead>
                            <tr>
                                <th>LRN</th>
                                <th>Name</th>
                                <th>Sex</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                <tr>
                                    <th>{{$student->lrn}}</th>
                                    <td>
                                        {{$student->last_name}},
                                        {{$student->first_name}}
                                        {{$student->middle_name}}
                                        {{$student->suffix}}
                                    </td>
                                    <td><center>{{$student->gender}} </center></td>
                                    <td>
                                        <center>
                                            <a href="/students/{{$student->id}}/edit" class="btn btn-primary btn-sm">
                                                <i class="fa fa-pen"></i>
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
@endsection