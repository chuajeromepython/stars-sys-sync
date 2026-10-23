@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('content')
    @include('layouts.message')
    @include('roles.form')
@endsection
