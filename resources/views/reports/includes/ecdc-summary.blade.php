<form id="ecdc-summary-form" method="POST" action="{{ route('reports.ecdc.store') }}">
    @csrf
    <div class="row">
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-header bg-white">
                    <h2 class="card-title font-weight-bold">ECDC consolidated reports</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Choose an organizational scope and assessment period to create a consolidated Excel report.</p>
                    <fieldset>
                        <legend class="h6 font-weight-bold mb-3">Report settings</legend>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="ecdc-scope">Report scope</label>
                    @if ($canSelectReportSchools)
                    <select id="ecdc-scope" name="scope" class="form-control" required>
                        <option value="school">School</option>
                        @if ($ecdcDistricts->isNotEmpty())
                            <option value="district">District</option>
                        @endif
                        @if ($ecdcDivisions->isNotEmpty())
                            <option value="division">Division</option>
                        @endif
                    </select>
                    @else
                        <input type="hidden" id="ecdc-scope" name="scope" value="school">
                        <p class="form-control-plaintext mb-0">School</p>
                    @endif
                </div>
                <div class="form-group col-md-6">
                    <label id="ecdc-scope-label" for="ecdc-scope-id">School</label>
                    @if ($canSelectReportSchools)
                    <select id="ecdc-scope-id" name="scope_id" class="form-control" required>
                        @foreach (['school' => $schools, 'district' => $ecdcDistricts, 'division' => $ecdcDivisions] as $type => $entities)
                            @foreach ($entities as $entity)
                                <option value="{{ $entity->id }}" data-scope="{{ $type }}" @disabled($type !== 'school') @if ($type !== 'school') hidden @endif>{{ $entity->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @else
                        <input type="hidden" id="ecdc-scope-id" name="scope_id" value="{{ $schools->first()?->id }}">
                        <p class="form-control-plaintext mb-0">{{ $schools->first()?->name ?? 'No school assigned' }}</p>
                        <small class="form-text text-muted">Your assigned school is automatically applied.</small>
                    @endif
                </div>
                <div class="form-group col-md-6">
                    <label for="ecdc-year">Academic year</label>
                    <select id="ecdc-year" name="academic_year_id" class="form-control" required>
                        @foreach ($ecdcYears as $year)
                            <option value="{{ $year->id }}" @selected($year->is_active)>{{ $year->from }}–{{ $year->to }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="ecdc-period">Assessment period</label>
                    <select id="ecdc-period" name="period" class="form-control" required>
                        <option value="1">BOSY</option>
                        <option value="3">EOSY</option>
                    </select>
                </div>
            </div>
                    </fieldset>
                </div>
                @can('reports.generate')
                    <div class="card-footer bg-white">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-excel mr-2" aria-hidden="true"></i>Generate ECDC report
                        </button>
                        <p class="small text-muted mt-3 mb-0">Generation runs in the background. You can continue using this page.</p>
                    </div>
                @endcan
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card">
                <div class="card-header bg-white">
                    <h2 class="card-title font-weight-bold">Your download</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <i class="far fa-file-excel fa-2x text-muted mr-3" aria-hidden="true"></i>
                        <div>
                            <h3 class="h6 font-weight-bold mb-1">Consolidated Excel workbook</h3>
                            <p class="small text-muted mb-0">Counts by age, interpretation, and sex, plus interpretation percentages within each age group.</p>
                        </div>
                    </div>
                    <div id="ecdc-summary-status" class="border rounded bg-light p-3" role="status" aria-live="polite">Generate a report to prepare your download.</div>
                    <a id="ecdc-summary-download" class="btn btn-success btn-block mt-3" hidden>Download ECDC report (.xlsx)</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="h6 font-weight-bold mb-3">How reports are grouped</h3>
                    <dl class="row small mb-0">
                        <dt class="col-4">School</dt><dd class="col-8">One row per section</dd>
                        <dt class="col-4">District</dt><dd class="col-8">One row per school</dd>
                        <dt class="col-4">Division</dt><dd class="col-8">One row per sub-office</dd>
                    </dl>
                    <p class="small text-muted border-top pt-3 mt-2 mb-0">Identical requests reuse a report for 10 minutes. You can request up to 3 reports per minute.</p>
                </div>
            </div>
        </div>
    </div>
</form>
