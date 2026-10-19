@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/ecdc_domains.js"></script>
@endsection

@section('content')
    @include('layouts.message')
    @can('ecdc_domains.manage')
        @include('ecdc_domains.create')
        @include('ecdc_domains.edit')
        @include('ecdc_domains.destroy')
    @endcan
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    @can('ecdc_domains.manage')
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#create_modal"><i class="fa fa-plus mr-2"></i> Add Domain</a>
                    @endcan
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-3" id="dt_ecdc_domains" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Competencies</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($domains as $domain)
                                <tr>
                                    <td>{{$domain->domain}}</td>
                                    <td>
                                        <a href="/ecdc_domains/{{$domain->id}}">
                                            {{$domain->competencies_count}} {{($domain->competencies_count == 1) ? 'competency' : 'competencies'}}
                                        </a>
                                    </td>
                                    <th>
                                        <center>
                                        <a href="/ecdc_domains/{{$domain->id}}" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @can('ecdc_domains.manage')
                                            <a href="#" class="btn-edit btn btn-primary btn-sm"
                                                data-toggle="modal" data-target="#edit_modal"
                                                data-edit_id="{{$domain->id}}"
                                                data-edit_domain="{{$domain->domain}}">
                                                <i class="fa fa-pen"></i>
                                            </a>
                                            <a href="#" class="btn-destroy btn-danger btn-sm btn"
                                                data-toggle="modal" data-target="#destroy_modal"
                                                data-destroy_id="{{$domain->id}}"
                                                data-destroy_domain="{{$domain->domain}}">
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
