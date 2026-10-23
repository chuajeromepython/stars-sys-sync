@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script src="/js/reports/school-first.js"></script>
    <script src="{{ asset('js/reports/ecdc-summary.js') }}" defer></script>
@endsection

@section('content')
    @include('layouts.message')
    <p class="text-muted mb-4">Choose a report, set your filters, and review or download the results.</p>

    <ul class="nav nav-pills bg-white border rounded p-2 mb-4" id="report-tabs" role="tablist" aria-label="Report type">
        <li class="nav-item mr-2" role="presentation">
            <a class="nav-link active" id="school-reports-tab" data-toggle="tab" href="#school-reports-panel"
                role="tab" aria-controls="school-reports-panel" aria-selected="true">
                School reports
            </a>
        </li>
        @if ($canViewEcdcReports)
            <li class="nav-item mr-2" role="presentation">
                <a class="nav-link" id="ecdc-reports-tab" data-toggle="tab" href="#ecdc-reports-panel"
                    role="tab" aria-controls="ecdc-reports-panel" aria-selected="false" tabindex="-1">
                    ECDC reports
                </a>
            </li>
        @endif
    </ul>

    <div class="tab-content" id="report-tab-content">
        <div class="tab-pane show active" id="school-reports-panel" role="tabpanel" aria-labelledby="school-reports-tab">
            <div class="row">
                <div class="col-12 col-xl-5">
                    <form id="school-report-form" class="card" method="POST" action="/reports/generate">
                        @csrf
                        <div class="card-header">
                            <h2 class="card-title font-weight-bold">School report settings</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                @if ($canSelectReportSchools)
                                    Choose your schools and assessment filters. Results are grouped separately for each school.
                                @else
                                    Choose your assessment filters. Your assigned school is automatically applied.
                                @endif
                            </p>

                            <div class="form-group">
                                @if ($canSelectReportSchools)
                                <label for="school_ids">Schools</label>
                                <select id="school_ids" name="school_ids[]" class="select2bs4 form-control" multiple required>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                                @else
                                    <span class="d-block font-weight-bold mb-2">Assigned school</span>
                                    <p class="form-control-plaintext mb-0">{{ $schools->first()?->name ?? 'No school assigned' }}</p>
                                    <small class="form-text text-muted">Your assigned school is automatically used for this report.</small>
                                @endif
                                @if ($schools->isEmpty())
                                    <small class="form-text text-danger">No school is assigned to this account.</small>
                                @endif
                            </div>

                            <fieldset class="border-top pt-3 mt-4">
                                <legend class="h6 font-weight-bold w-auto pr-3">Assessment filters</legend>
                                <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="education_level_id">Education level</label>
                                <select id="education_level_id" name="education_level_id" class="form-control" required>
                                    <option value="">Select education level</option>
                                    <option value="1">Elementary</option>
                                    <option value="2">Junior High School</option>
                                    <option value="3">Senior High School</option>
                                </select>
                            </div>

                            <div class="form-group col-sm-6">
                                <label for="period_id">Period</label>
                                <select id="period_id" name="period_id" class="form-control" required>
                                    <option value="">Select period</option>
                                    @foreach ($periods as $period)
                                        <option value="{{ $period->id }}">{{ $period->period }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-sm-6">
                                <label for="grade_level_id">Grade level</label>
                                <select id="grade_level_id" name="grade_level_id" class="form-control" required>
                                    <option value="">Select grade level</option>
                                    @foreach ($gradeLevels as $gradeLevel)
                                        <option value="{{ $gradeLevel->id }}" data-grade="{{ (int) str_replace('Grade ', '', $gradeLevel->level) }}">{{ $gradeLevel->level }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-sm-6">
                                <label for="subject_id">Subject</label>
                                <select id="subject_id" name="subject_id" class="form-control">
                                    <option value="">Select subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-sm-6">
                                <label for="assessment_type_id">Assessment type</label>
                                <select id="assessment_type_id" name="assessment_type_id" class="form-control" required>
                                    <option value="">Select assessment type</option>
                                    @foreach ($assessmentTypes as $assessmentType)
                                        <option value="{{ $assessmentType->id }}">{{ $assessmentType->type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-sm-6">
                                <label for="assessment_date">Date of assessment</label>
                                <input id="assessment_date" name="assessment_date" class="form-control" type="date" required>
                            </div>

                            <div class="form-group col-sm-6">
                                <label for="report_type_id">Report type</label>
                                <select id="report_type_id" name="report_type_id" class="form-control" required>
                                    <option value="">Select report type</option>
                                    <option value="LC">Level of Competencies</option>
                                    <option value="AL">Achievement Level</option>
                                    <option value="SA">Score Analysis</option>
                                </select>
                            </div>

                                </div>
                            </fieldset>

                            <details id="senior-high-report-filters" class="border rounded p-3 mt-2 mb-3" hidden>
                                <summary class="font-weight-bold">Senior high school filters <small class="text-muted font-weight-normal">(optional)</small></summary>
                                <div class="form-group mt-3">
                                    <label for="track_id">Track</label>
                                    <select id="track_id" name="track_id" class="form-control" disabled>
                                        <option value="">All tracks</option>
                                        @foreach ($tracks as $track)
                                            <option value="{{ $track->id }}">{{ $track->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="strand_id">Strand</label>
                                    <select id="strand_id" name="strand_id" class="form-control" disabled>
                                        <option value="">All strands</option>
                                        @foreach ($strands as $strand)
                                            <option value="{{ $strand->id }}">{{ $strand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="semester_id">Semester</label>
                                    <select id="semester_id" name="semester_id" class="form-control" disabled>
                                        <option value="">All semesters</option>
                                        @foreach ($semesters as $semester)
                                            <option value="{{ $semester->id }}">{{ $semester->semester }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </details>
                        </div>
                        <div class="card-footer bg-white">
                            <button id="generate-school-reports" class="btn btn-primary btn-block" type="submit"
                                @disabled($schools->isEmpty())>
                                <i class="fas fa-file-alt mr-2" aria-hidden="true"></i>
                                Generate school reports
                            </button>
                            <p id="school-report-feedback" class="small text-muted mt-3 mb-0" role="status" aria-live="polite">Your selected filters will be applied to the report.</p>
                        </div>
                    </form>
                </div>

                <div class="col-12 col-xl-7" id="school-report-results" aria-live="polite">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h2 class="card-title font-weight-bold">Report results</h2>
                        </div>
                        <div class="card-body text-center py-5">
                            <i class="far fa-file-alt fa-2x text-muted mb-3" aria-hidden="true"></i>
                            <h3 class="h5">Ready when you are</h3>
                            <p class="text-muted mb-1">Set your assessment filters, then generate a report.</p>
                            <p class="small text-muted mb-0">Results will appear here, grouped separately by school.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if ($canViewEcdcReports)
            <div class="tab-pane" id="ecdc-reports-panel" role="tabpanel" aria-labelledby="ecdc-reports-tab">
                @include('reports.includes.ecdc-summary')
            </div>
        @endif
    </div>
@endsection
