@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('content')
    @include('layouts.message')
    @include('layouts.user-management-tabs')
    @include('permissions.form', ['action' => route('permissions.update', $permission), 'method' => 'PUT'])
@endsection
