@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/ecdc_domains.js"></script>
@endsection

@section('content')
    @include('layouts.message')
    @can('ecdc_domains.manage')
        @include('ecdc_domains.competencies.create')
        @include('ecdc_domains.competencies.edit')
        @include('ecdc_domains.competencies.destroy')
    @endcan
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="/ecdc_domains" class="btn btn-secondary"><i class="fa fa-arrow-left mr-2"></i> Back to Domains</a>
                    @can('ecdc_domains.manage')
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#create_competency_modal"><i class="fa fa-plus mr-2"></i> Add Competency</a>
                    @endcan
                </div>
                <div class="card-body">
                    <label class="text-primary">{{$ecdc_domain->domain}}</label>
                    <table class="table table-bordered mb-3" id="dt_ecdc_competencies" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Competency</th>
                                <th>Used In</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($competencies as $competency)
                                <tr>
                                    <td>{{$competency->id}}</td>
                                    <td>{{$competency->competency}}</td>
                                    <td>
                                        @if($competency->student_results_count > 0)
                                            <span class="badge badge-success">{{$competency->student_results_count}} {{($competency->student_results_count == 1) ? 'result' : 'results'}}</span>
                                        @else
                                            <span class="badge badge-secondary">Not yet used</span>
                                        @endif
                                    </td>
                                    <th>
                                        <center>
                                        @can('ecdc_domains.manage')
                                            <a href="#" class="btn-edit btn btn-primary btn-sm"
                                                data-toggle="modal" data-target="#edit_competency_modal"
                                                data-edit_id="{{$competency->id}}"
                                                data-edit_competency="{{$competency->competency}}">
                                                <i class="fa fa-pen"></i>
                                            </a>
                                            <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                                data-toggle="modal" data-target="#destroy_competency_modal"
                                                data-destroy_id="{{$competency->id}}"
                                                data-destroy_competency="{{$competency->competency}}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        @endcan
                                        </center>
                                    </th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
