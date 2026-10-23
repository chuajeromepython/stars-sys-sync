@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/students.js"></script>
@endsection

@section('content')
	@include('layouts.message')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    @can('students.manage')
                    <a href="/students/create" class="btn btn-primary" ><i class="fa fa-plus mr-2"></i> Add Student</a>
                    @endcan
                </div>
                <div class="card-body" >
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="student-academic-year">Academic Year</label>
                            <select class="form-control" id="student-academic-year" name="academic_year_id">
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}" @selected($selectedAcademicYearId === (int) $year->id)>{{ $year->from }} - {{ $year->to }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if (in_array($hierarchy['scope'], ['system', 'division']))
                            <div class="col-md-3">
                                <label for="student-district">District</label>
                                <select class="form-control" id="student-district" name="district_id">
                                    <option value="">All districts</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if (in_array($hierarchy['scope'], ['system', 'division', 'district']))
                            <div class="col-md-3">
                                <label for="student-school">School</label>
                                <select class="form-control" id="student-school" name="school_id">
                                    <option value="">All schools</option>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}" data-district="{{ $school->district_id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if (in_array($hierarchy['scope'], ['system', 'division', 'district', 'school']))
                            <div class="col-md-3">
                                <label for="student-teacher">Teacher</label>
                                <select class="form-control" id="student-teacher" name="teacher_id">
                                    <option value="">All teachers</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" data-school="{{ $teacher->school_id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <table class="table table-bordered mb-3" id="dt_students">
                        <thead>
                            <tr>
                                <th>LRN</th>
                                <th>Name</th>
                                <th>Sex</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
