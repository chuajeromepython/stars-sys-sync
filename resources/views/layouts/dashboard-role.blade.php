@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    @if (count($recordTypes) > 0)
        <script src="/js/dashboard/role-dashboard.js"></script>
    @endif
    <script>
        $(function () {
            $('#dashboard-academic-year').on('change', function () {
                this.form.requestSubmit();
            });
        });
    </script>
@endsection

@section('page_css')
    <style>
        .role-dashboard {
            max-width: 1440px;
            margin: 0 auto;
        }

        .role-dashboard-welcome {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 22px;
            padding: 32px 36px;
            color: #fff;
            background: linear-gradient(120deg, #172b63 0%, #2457d6 56%, #0e9f9a 100%);
            box-shadow: 0 18px 40px rgba(36, 87, 214, .18);
        }

        .role-dashboard-welcome::after {
            content: '';
            position: absolute;
            width: 260px;
            height: 260px;
            right: -70px;
            top: -110px;
            border: 38px solid rgba(255, 255, 255, .1);
            border-radius: 50%;
            pointer-events: none;
        }

        .role-dashboard-eyebrow,
        .role-dashboard-context-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .role-dashboard-eyebrow {
            color: rgba(255, 255, 255, .7);
        }

        .role-dashboard-welcome h1 {
            margin: .35rem 0;
            font-size: 2rem;
            font-weight: 800;
        }

        .role-dashboard-welcome p {
            max-width: 690px;
            color: rgba(255, 255, 255, .78);
        }

        .role-dashboard-date {
            position: relative;
            z-index: 1;
            text-align: right;
            color: rgba(255, 255, 255, .75);
        }

        .role-dashboard-avatar {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, .3);
            border-radius: 50%;
        }

        .role-dashboard-context-card {
            height: 100%;
            border: 0;
            border-left: 4px solid #2457d6;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 25px rgba(23, 35, 60, .06);
        }

        .role-dashboard-context-card.academic {
            border-left-color: #f2b84b;
        }

        .role-dashboard-context-card.school {
            border-left-color: #0e9f9a;
        }

        .role-dashboard-context-icon {
            width: 42px;
            height: 42px;
            display: grid;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            color: #fff;
            background: #2457d6;
        }

        .academic .role-dashboard-context-icon {
            background: #f2b84b;
        }

        .school .role-dashboard-context-icon {
            background: #0e9f9a;
        }

        .role-dashboard-context-label {
            color: #71809b;
            letter-spacing: .08em;
        }

        .role-dashboard-context-value {
            color: #17233c;
            font-size: 1.05rem;
            font-weight: 750;
        }

        .role-dashboard-academic-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .role-dashboard-academic-select {
            width: min(190px, 48%);
            flex: 0 0 min(190px, 48%);
        }

        .role-dashboard-card,
        .role-dashboard-panel {
            border: 0;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(23, 35, 60, .06);
        }

        .role-dashboard-card {
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        .role-dashboard-card::after {
            content: '';
            position: absolute;
            right: -22px;
            bottom: -34px;
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: rgba(36, 87, 214, .06);
        }

        .role-dashboard-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: #2457d6;
            background: #edf2ff;
        }

        .role-dashboard-value {
            color: #17233c;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .role-dashboard-muted {
            color: #71809b;
        }

        .role-dashboard-progress {
            height: 10px;
            border-radius: 10px;
            background: #edf2f8;
        }

        .role-dashboard-progress .progress-bar {
            border-radius: 10px;
            background-color: #2457d6;
        }

        .role-dashboard-panel .card-header {
            border-bottom: 1px solid #edf0f5;
            background: transparent;
        }

        @media (max-width: 575.98px) {
            .role-dashboard-welcome {
                border-radius: 14px;
                padding: 24px;
            }

            .role-dashboard-value {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 767.98px) {
            .role-dashboard-welcome h1 {
                font-size: 1.55rem;
            }

            .role-dashboard-date {
                margin-top: 18px;
                text-align: left;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid role-dashboard">
        <section class="role-dashboard-welcome mb-4">
            <div class="row align-items-center position-relative" style="z-index: 1">
                <div class="col-md-8 d-flex align-items-center">
                    @if ($welcomeContext['gender'])
                        <img class="role-dashboard-avatar mr-3 d-none d-sm-block"
                            src="/images/{{ $welcomeContext['gender'] }}.png"
                            alt="Profile image">
                    @endif
                    <div>
                        <div class="role-dashboard-eyebrow">STARS / Daily briefing</div>
                        <h1>Welcome, {{ $welcomeContext['full_name'] }}.</h1>
                        <div class="font-weight-bold mb-1">{{ $dashboard['heading'] }}</div>
                        <p class="mb-0">{{ $dashboard['summary'] }}</p>
                    </div>
                </div>
                <div class="col-md-4 role-dashboard-date">
                    <span class="role-dashboard-eyebrow">{{ now()->format('l, F j, Y') }}</span>
                    <div class="mt-2">{{ $dashboard['role'] }}</div>
                    <div class="mt-2">
                        <span class="badge badge-light px-3 py-2">{{ $dashboard['scope_label'] }}</span>
                        @if ($dashboard['read_only'])
                            <span class="badge badge-warning px-3 py-2 ml-1">Read only</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="row mb-4">
            <div class="col-lg-4 mb-3 mb-lg-0">
                <div class="role-dashboard-context-card academic p-3">
                    <div class="d-flex align-items-center">
                        <span class="role-dashboard-context-icon mr-3" aria-hidden="true">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <form action="/dashboard" method="GET" class="flex-grow-1">
                            <div class="role-dashboard-academic-heading">
                                <div>
                                    <label class="role-dashboard-context-label mb-1" for="dashboard-academic-year">
                                        Academic year
                                    </label>
                                    <div class="role-dashboard-context-value">
                                        {{ $academicYear ? $academicYear->from.' - '.$academicYear->to : 'None' }}
                                    </div>
                                </div>
                                <select class="form-control role-dashboard-academic-select"
                                    id="dashboard-academic-year"
                                    name="academic_year_id"
                                    aria-label="Filter by academic year">
                                    @forelse ($academicYears as $year)
                                        <option value="{{ $year->id }}" @selected($academicYear?->id === $year->id)>
                                            {{ $year->from }} - {{ $year->to }}{{ $year->is_active ? ' (Active)' : '' }}
                                        </option>
                                    @empty
                                        <option value="">Not set</option>
                                    @endforelse
                                </select>
                            </div>
                            <noscript>
                                <button class="btn btn-primary btn-sm mt-2" type="submit">Apply academic year</button>
                            </noscript>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="role-dashboard-context-card school p-3">
                    <div class="d-flex align-items-center">
                        <span class="role-dashboard-context-icon mr-3" aria-hidden="true">
                            <i class="fas fa-school"></i>
                        </span>
                        <div>
                            <div class="role-dashboard-context-label">
                                {{ $welcomeContext['level'] ? $welcomeContext['level'].' context' : 'School details' }}
                            </div>
                            <div class="role-dashboard-context-value">
                                {{ $welcomeContext['school'] ?? ($welcomeContext['system_wide'] ? 'All schools' : ($welcomeContext['district'] ?? ($welcomeContext['division'] ?? 'Assignment not configured'))) }}
                            </div>
                            <small class="role-dashboard-muted">
                                @if ($welcomeContext['level'] === 'School')
                                    {{ $welcomeContext['district'] }}, {{ $welcomeContext['division'] }}
                                    @if ($welcomeContext['school_code'])
                                        &middot; {{ $welcomeContext['school_code'] }}
                                    @endif
                                @elseif ($welcomeContext['level'] === 'District')
                                    {{ $welcomeContext['division'] }} &middot; {{ $welcomeContext['district'] }}
                                @elseif ($welcomeContext['level'] === 'Division')
                                    {{ $welcomeContext['division'] }}
                                @elseif ($welcomeContext['system_wide'])
                                    System-wide access
                                @else
                                    No organizational assignment is linked to this account.
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if ($dashboard['metrics'] === [])
            <div class="alert alert-info" role="status">
                No dashboard summaries are available for this account's current assignment.
            </div>
        @else
            <div class="row">
                @foreach ($dashboard['metrics'] as $metric)
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <section class="card role-dashboard-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="role-dashboard-muted small">{{ $metric['label'] }}</div>
                                        <div class="role-dashboard-value mt-2">{{ number_format($metric['value']) }}</div>
                                    </div>
                                    <span class="role-dashboard-icon" aria-hidden="true">
                                        <i class="{{ $metric['icon'] }}"></i>
                                    </span>
                                </div>
                            </div>
                        </section>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($dashboard['distribution'] !== [])
            <section class="card role-dashboard-panel mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">{{ $dashboard['distribution_title'] }}</h3>
                </div>
                <div class="card-body">
                    @foreach ($dashboard['distribution'] as $item)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ $item['label'] }}</span>
                                <strong>{{ number_format($item['value']) }}</strong>
                            </div>
                            <div class="progress role-dashboard-progress"
                                role="progressbar"
                                aria-label="{{ $item['label'] }}"
                                aria-valuenow="{{ $item['percentage'] }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                                <div class="progress-bar" style="width: {{ $item['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if (count($recordTypes) > 0)
            <section class="card role-dashboard-panel mb-4">
                <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
                    <div>
                        <h3 class="card-title font-weight-bold">Records in your scope</h3>
                        <div class="role-dashboard-muted small mt-1">Only records permitted for your role are listed.</div>
                    </div>
                    <div class="mt-3 mt-sm-0">
                        <label for="dashboard-record-type" class="sr-only">Record type</label>
                        <select id="dashboard-record-type" class="form-control"
                            aria-label="Choose record type">
                            @foreach (['teachers' => 'Teachers', 'students' => 'Students', 'classrooms' => 'Classrooms'] as $value => $label)
                                @if (in_array($value, $recordTypes, true))
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <table id="dashboard-records-table" class="table table-striped table-bordered w-100"
                        data-records-url="/dashboard/records">
                        <thead>
                            <tr>
                                <th>Record</th>
                                <th>Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>
        @endif

        @if ($dashboard['role'] === \App\Enums\Role::DivisionAdministrator->value)
            <section class="card role-dashboard-panel">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">System setup</h3>
                </div>
                <div class="card-body d-flex flex-wrap">
                    @can('academic_years.view')
                        <a class="btn btn-outline-primary mr-2 mb-2" href="/academic_years">Academic years</a>
                    @endcan
                    @can('users.view')
                        <a class="btn btn-outline-primary mr-2 mb-2" href="/users">Users</a>
                    @endcan
                    @can('roles.view')
                        <a class="btn btn-outline-primary mr-2 mb-2" href="/roles">Roles</a>
                    @endcan
                    @can('permissions.view')
                        <a class="btn btn-outline-primary mr-2 mb-2" href="/permissions">Permissions</a>
                    @endcan
                    @can('schools.view')
                        <a class="btn btn-outline-primary mr-2 mb-2" href="/schools">Schools</a>
                    @endcan
                    @can('districts.view')
                        <a class="btn btn-outline-primary mr-2 mb-2" href="/districts">Districts</a>
                    @endcan
                </div>
            </section>
        @endif
    </div>
@endsection
