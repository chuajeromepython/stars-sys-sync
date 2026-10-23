@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script src="/js/reports/school-first.js"></script>
@endsection

@section('content')
    @include('layouts.message')

    <div class="row">
        <div class="col-12 col-xl-4">
            <form id="school-report-form" class="card" method="POST" action="/reports/generate">
                @csrf
                <div class="card-header">
                    <h2 class="card-title text-primary font-weight-bold">Generate school reports</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Select one or more authorized schools first. Each school will receive a separate report.
                    </p>

                    <div class="form-group">
                        <label for="school_ids">Schools</label>
                        <select id="school_ids" name="school_ids[]" class="select2bs4 form-control" multiple required>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                        @if ($schools->isEmpty())
                            <small class="form-text text-danger">No school is assigned to this account.</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="education_level_id">Education level</label>
                        <select id="education_level_id" name="education_level_id" class="form-control" required>
                            <option value="">Select education level</option>
                            <option value="1">Elementary</option>
                            <option value="2">Junior High School</option>
                            <option value="3">Senior High School</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="period_id">Period</label>
                        <select id="period_id" name="period_id" class="form-control" required>
                            <option value="">Select period</option>
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}">{{ $period->period }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="grade_level_id">Grade level</label>
                        <select id="grade_level_id" name="grade_level_id" class="form-control" required>
                            <option value="">Select grade level</option>
                            @foreach ($gradeLevels as $gradeLevel)
                                <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->level }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject_id">Subject</label>
                        <select id="subject_id" name="subject_id" class="form-control">
                            <option value="">All subjects</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="assessment_type_id">Assessment type</label>
                        <select id="assessment_type_id" name="assessment_type_id" class="form-control" required>
                            <option value="">Select assessment type</option>
                            @foreach ($assessmentTypes as $assessmentType)
                                <option value="{{ $assessmentType->id }}">{{ $assessmentType->type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="assessment_date">Date of assessment</label>
                        <input id="assessment_date" name="assessment_date" class="form-control" type="date" required>
                    </div>

                    <div class="form-group">
                        <label for="report_type_id">Report type</label>
                        <select id="report_type_id" name="report_type_id" class="form-control" required>
                            <option value="">Select report type</option>
                            <option value="LC">Level of Competencies</option>
                            <option value="AL">Achievement Level</option>
                            <option value="SA">Score Analysis</option>
                        </select>
                    </div>

                    <details class="mb-3">
                        <summary class="text-primary">Senior high school filters</summary>
                        <div class="form-group mt-3">
                            <label for="track_id">Track</label>
                            <select id="track_id" name="track_id" class="form-control">
                                <option value="">All tracks</option>
                                @foreach ($tracks as $track)
                                    <option value="{{ $track->id }}">{{ $track->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="strand_id">Strand</label>
                            <select id="strand_id" name="strand_id" class="form-control">
                                <option value="">All strands</option>
                                @foreach ($strands as $strand)
                                    <option value="{{ $strand->id }}">{{ $strand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="semester_id">Semester</label>
                            <select id="semester_id" name="semester_id" class="form-control">
                                <option value="">All semesters</option>
                                @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->semester }}</option>
                                @endforeach
                            </select>
                        </div>
                    </details>
                </div>
                <div class="card-footer">
                    <button id="generate-school-reports" class="btn btn-primary btn-block" type="submit"
                        @disabled($schools->isEmpty())>
                        <i class="fas fa-file-alt mr-2" aria-hidden="true"></i>
                        Generate reports
                    </button>
                </div>
            </form>
        </div>

        <div class="col-12 col-xl-8" id="school-report-results" aria-live="polite">
            <div class="card">
                <div class="card-body text-muted">
                    Reports will appear here, grouped separately by school.
                </div>
            </div>
        </div>
    </div>
@endsection
