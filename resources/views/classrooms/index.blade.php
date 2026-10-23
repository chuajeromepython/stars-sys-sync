@extends('layouts.master')

@section('page_name', $page['name'])
@section('page_title', $page['title'])

@section('page_css')
<style>
    .classroom-directory { --navy:#17324d; --teal:#1f6f8b; --gold:#f0b44d; --border:#dfe7ee; color:#263746; }
    .classroom-toolbar { align-items:center; background:#fff; border:1px solid var(--border); border-left:5px solid var(--teal); border-radius:12px; box-shadow:0 8px 24px rgba(23,50,77,.08); display:flex; gap:24px; justify-content:space-between; margin-bottom:24px; padding:22px 24px; }
    .classroom-toolbar__eyebrow { color:var(--teal); display:block; font-size:.72rem; font-weight:800; letter-spacing:.13em; margin-bottom:5px; text-transform:uppercase; }
    .classroom-toolbar h2 { color:var(--navy); font-size:1.65rem; font-weight:700; margin:0 0 5px; }
    .classroom-toolbar p { color:#6b7d8c; margin:0; }
    .classroom-toolbar__meta,.classroom-toolbar__actions { align-items:center; display:flex; flex-wrap:wrap; gap:10px; }
    .classroom-toolbar__meta { margin-top:13px; }
    .directory-stat { background:#f4f7fa; border:1px solid var(--border); border-radius:999px; color:#53697a; font-size:.78rem; font-weight:700; padding:6px 11px; }
    .directory-stat strong { color:var(--navy); margin-right:4px; }
    .classroom-toolbar__actions .btn { border-radius:8px; font-weight:700; padding:9px 15px; white-space:nowrap; }
    .btn-directory-primary { background:var(--navy); border-color:var(--navy); color:#fff; }
    .btn-directory-primary:hover,.btn-directory-primary:focus { background:#0f263b; border-color:#0f263b; color:#fff; }
    .btn-directory-secondary { background:#e9f4f7; border-color:#cde4ea; color:#185b70; }
    .btn-directory-secondary:hover,.btn-directory-secondary:focus { background:#dceef2; border-color:#b7d9e1; color:#12495a; }
    .classroom-grade { background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow:0 5px 18px rgba(23,50,77,.055); margin-bottom:20px; overflow:hidden; }
    .classroom-grade__header { align-items:center; background:#f8fafc; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; padding:15px 20px; }
    .classroom-grade__title { align-items:center; color:var(--navy); display:flex; font-size:1rem; font-weight:800; margin:0; }
    .classroom-grade__title::before { background:var(--gold); border-radius:50%; content:''; height:9px; margin-right:10px; width:9px; }
    .classroom-grade__count { color:#718291; font-size:.78rem; font-weight:700; }
    .classroom-grid { display:grid; gap:16px; grid-template-columns:repeat(4,minmax(0,1fr)); padding:20px; }
    .classroom-tile { background:#fff; border:1px solid var(--border); border-radius:10px; box-shadow:0 3px 10px rgba(23,50,77,.045); min-width:0; overflow:hidden; position:relative; transition:border-color 180ms ease,box-shadow 180ms ease,transform 180ms ease; }
    .classroom-tile:hover,.classroom-tile:focus-within { border-color:#a9ccd7; box-shadow:0 10px 22px rgba(23,50,77,.12); transform:translateY(-3px); }
    .classroom-tile__top { align-items:center; display:flex; justify-content:space-between; min-height:45px; padding:10px 12px 0; }
    .classroom-tile__badge { border-radius:999px; font-size:.66rem; font-weight:800; letter-spacing:.06em; padding:6px 9px; }
    .classroom-tile__badge--advisory { background:#e3eefb; color:#245d96; }
    .classroom-tile__badge--subject { background:#e3f4ec; color:#247052; }
    .classroom-tile__menu { border-radius:7px; color:#718291; padding:7px 9px; }
    .classroom-tile__menu:hover,.classroom-tile__menu:focus { background:#f4f7fa; color:var(--navy); }
    .classroom-tile__body { cursor:pointer; outline:none; padding:14px 18px 17px; }
    .classroom-tile__body:focus-visible { box-shadow:inset 0 0 0 3px rgba(31,111,139,.28); }
    .classroom-tile__identity { margin-bottom:16px; }
    .classroom-tile__name { color:var(--navy); font-size:1.18rem; font-weight:700; line-height:1.2; margin:0; overflow-wrap:anywhere; }
    .classroom-tile__details { border-top:1px solid #edf1f4; margin:0; padding-top:13px; }
    .classroom-tile__detail { align-items:flex-start; color:#667a8a; display:flex; font-size:.82rem; gap:9px; line-height:1.4; margin-bottom:7px; }
    .classroom-tile__detail:last-child { margin-bottom:0; }
    .classroom-tile__detail i { color:var(--teal); margin-top:3px; text-align:center; width:13px; }
    .classroom-tile__footer { align-items:center; color:var(--teal); display:flex; font-size:.75rem; font-weight:800; justify-content:space-between; letter-spacing:.04em; margin-top:16px; text-transform:uppercase; }
    .classroom-empty { background:#fff; border:1px dashed #b8cad7; border-radius:12px; color:#627787; padding:52px 24px; text-align:center; }
    .classroom-empty__icon { align-items:center; background:#e9f4f7; border-radius:50%; color:var(--teal); display:inline-flex; font-size:1.35rem; height:54px; justify-content:center; margin-bottom:14px; width:54px; }
    .classroom-empty h3 { color:var(--navy); font-size:1.1rem; font-weight:800; margin-bottom:7px; }
    .spinner { animation:spin 2000ms linear infinite; }
    @keyframes spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
    @media (max-width:1199px) { .classroom-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:991px) { .classroom-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:767px) { .classroom-toolbar { align-items:stretch; flex-direction:column; padding:19px; } .classroom-toolbar__actions .btn { flex:1 1 auto; } .classroom-grid { grid-template-columns:1fr; padding:14px; } }
    @media (prefers-reduced-motion:reduce) { .classroom-tile { transition:none; } .spinner { animation:none; } }
</style>
@endsection

@section('page_script')
<script>var school_id = {{ $school_id }};</script>
<script src="/js/classrooms.js"></script>
<script>
    $(document).on('keydown', '.div-room', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            $(this).trigger('click');
        }
    });
</script>
@endsection

@section('content')
@include('layouts.message')
@include('classrooms.upload')
@include('classrooms.edit')
@include('classrooms.destroy')

@php
    $classroomCount = collect($classrooms)->sum(fn ($rooms) => count($rooms));
    $gradeLevelCount = count($classrooms);
@endphp

<div class="container-fluid classroom-directory">
    <section class="classroom-toolbar" aria-labelledby="classroom-directory-title">
        <div>
            <span class="classroom-toolbar__eyebrow">Classroom Directory</span>
            <h2 id="classroom-directory-title">Manage your learning spaces</h2>
            <p>Open a classroom to view its learners and subjects.</p>
            <div class="classroom-toolbar__meta" aria-label="Directory summary">
                <span class="directory-stat"><strong>{{ $classroomCount }}</strong> {{ Str::plural('classroom', $classroomCount) }}</span>
                <span class="directory-stat"><strong>{{ $gradeLevelCount }}</strong> grade {{ Str::plural('level', $gradeLevelCount) }}</span>
            </div>
        </div>
        @can('classrooms.manage')
            <div class="classroom-toolbar__actions">
                <a href="/classrooms/create" class="btn btn-directory-primary"><i class="fa fa-plus mr-2"></i>Add Classroom</a>
                <button type="button" class="btn btn-directory-secondary" data-toggle="modal" data-target="#upload_modal_advisory"><i class="fa fa-upload mr-2"></i>Upload Advisory Class</button>
            </div>
        @endcan
    </section>

    @if ($classroomCount === 0)
        <section class="classroom-empty">
            <div class="classroom-empty__icon"><i class="fa fa-school"></i></div>
            <h3>No classrooms found</h3>
            <p class="mb-0">{{ $message }}</p>
        </section>
    @endif

    @foreach ($classrooms as $gradeLevel => $rooms)
        <section class="classroom-grade" aria-labelledby="grade-{{ $loop->index }}">
            <header class="classroom-grade__header">
                <h3 class="classroom-grade__title" id="grade-{{ $loop->index }}">{{ $gradeLevel }}</h3>
                <span class="classroom-grade__count">{{ count($rooms) }} {{ Str::plural('classroom', count($rooms)) }}</span>
            </header>
            <div class="classroom-grid">
                @foreach ($rooms as $room)
                    <article class="classroom-tile">
                        <div class="classroom-tile__top">
                            @if (Auth::user()->classification === 'Teacher')
                                @if ($room['is_advisory'] == 1)
                                    <span class="classroom-tile__badge classroom-tile__badge--advisory">ADVISORY</span>
                                @else
                                    <span class="classroom-tile__badge classroom-tile__badge--subject">SUBJECT CLASS ({{ $room['classes'] }})</span>
                                @endif
                            @else
                                <span></span>
                                <a class="classroom-tile__menu" data-toggle="dropdown" href="#" aria-label="Classroom settings"><i class="fa fa-ellipsis-h"></i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <span class="dropdown-item dropdown-header">Classroom Settings</span>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" data-classroom_id="{{ $room['classroom_id'] }}" data-section="{{ $room['section'] }}" data-advisor="{{ $room['advisor'] }}" class="dropdown-item btn-edit" data-toggle="modal" data-target="#edit_modal"><i class="fas fa-pen mr-2"></i>Edit</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" data-classroom_id="{{ $room['classroom_id'] }}" data-section="{{ $room['section'] }}" data-grade_level="{{ $gradeLevel }}" data-toggle="modal" data-target="#destroy_modal" class="dropdown-item btn-destroy"><i class="fas fa-trash mr-2"></i>Delete <span class="float-right badge badge-danger"><i class="fa fa-exclamation"></i></span></a>
                                </div>
                            @endif
                        </div>
                        <div class="classroom-tile__body div-room" data-id="{{ $room['classroom_id'] }}" role="link" tabindex="0" aria-label="Open {{ $room['section'] }} classroom">
                            <div class="classroom-tile__identity">
                                <h4 class="classroom-tile__name">{{ $room['section'] }}</h4>
                            </div>
                            <div class="classroom-tile__details">
                                <div class="classroom-tile__detail"><i class="fa fa-user-tie"></i><span>{{ $room['advisor'] ?: 'No adviser assigned' }}</span></div>
                                <div class="classroom-tile__detail"><i class="fa fa-book-open"></i><span>{{ $room['subject'] === 'Edukasyong Pantahanan at Pangkabuhayan (EPP)' ? 'EPP' : ($room['subject'] ?: 'All subjects') }}</span></div>
                            </div>
                            <div class="classroom-tile__footer"><span>Open classroom</span><i class="fa fa-arrow-right"></i></div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
@endsection
