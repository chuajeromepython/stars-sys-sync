@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_css')
    <style>
        :root {
            --dashboard-ink: #17233c;
            --dashboard-muted: #71809b;
            --dashboard-blue: #2457d6;
            --dashboard-teal: #0e9f9a;
            --dashboard-gold: #f2b84b;
            --dashboard-background: #f4f7fb
        }

        .dashboard-shell {
            max-width: 1440px;
            margin: 0 auto;
            color: var(--dashboard-ink)
        }

        .dashboard-welcome {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            padding: 32px 36px;
            color: #fff;
            background: linear-gradient(120deg, #172b63 0%, #2457d6 56%, #0e9f9a 100%);
            box-shadow: 0 18px 40px rgba(36, 87, 214, .18)
        }

        .dashboard-welcome:after {
            content: '';
            position: absolute;
            width: 260px;
            height: 260px;
            right: -70px;
            top: -110px;
            border: 38px solid rgba(255, 255, 255, .1);
            border-radius: 50%
        }

        .dashboard-welcome .eyebrow,
        .dashboard-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase
        }

        .dashboard-welcome .eyebrow {
            color: rgba(255, 255, 255, .7)
        }

        .dashboard-welcome h1 {
            margin: .35rem 0;
            font-size: 2rem;
            font-weight: 800
        }

        .dashboard-welcome p {
            max-width: 690px;
            color: rgba(255, 255, 255, .78)
        }

        .dashboard-date {
            position: relative;
            z-index: 1;
            text-align: right;
            color: rgba(255, 255, 255, .75)
        }

        .dashboard-avatar {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, .3);
            border-radius: 50%
        }

        .dashboard-context-card,
        .dashboard-stat-card,
        .dashboard-search-card,
        .dashboard-template-card {
            border: 0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 25px rgba(23, 35, 60, .06)
        }

        .dashboard-context-card {
            height: 100%;
            border-left: 4px solid var(--dashboard-blue)
        }

        .dashboard-context-card.academic {
            border-left-color: var(--dashboard-gold)
        }

        .dashboard-context-card.school {
            border-left-color: var(--dashboard-teal)
        }

        .dashboard-context-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            color: #fff;
            background: var(--dashboard-blue)
        }

        .academic .dashboard-context-icon {
            background: var(--dashboard-gold)
        }

        .school .dashboard-context-icon {
            background: var(--dashboard-teal)
        }

        .dashboard-context-value {
            font-size: 1.05rem;
            font-weight: 750
        }

        .dashboard-stat-card {
            position: relative;
            overflow: hidden;
            height: 100%
        }

        .dashboard-stat-card:after {
            content: '';
            position: absolute;
            right: -24px;
            bottom: -38px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: rgba(36, 87, 214, .06)
        }

        .dashboard-stat-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: var(--dashboard-blue);
            background: #edf2ff
        }

        .dashboard-stat-card:nth-child(2) .dashboard-stat-icon {
            color: var(--dashboard-teal);
            background: #e8f8f6
        }

        .dashboard-stat-card:nth-child(3) .dashboard-stat-icon {
            color: #9b6b00;
            background: #fff5dd
        }

        .dashboard-stat-card:nth-child(4) .dashboard-stat-icon {
            color: #a44b77;
            background: #fdebf2
        }

        .dashboard-stat-value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1
        }

        .dashboard-search-input {
            border: 0;
            border-radius: 12px;
            background: var(--dashboard-background);
            box-shadow: none
        }

        .dashboard-search-input:focus {
            box-shadow: 0 0 0 2px rgba(36, 87, 214, .18)
        }

        .dashboard-search-item {
            border-top: 1px solid #edf0f5
        }

        .dashboard-search-item:first-child {
            border-top: 0
        }

        .dashboard-search-item .search-avatar {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            color: var(--dashboard-blue);
            background: #edf2ff
        }

        .dashboard-template-card .card-header {
            border-bottom-color: #edf0f5;
            background: transparent
        }

        .dashboard-template-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .65rem .8rem;
            border-radius: 10px;
            color: var(--dashboard-ink)
        }

        .dashboard-template-link:hover {
            background: #f5f8ff;
            color: var(--dashboard-blue)
        }

        .dashboard-muted {
            color: var(--dashboard-muted) !important
        }

        @media(max-width:767px) {
            .dashboard-welcome {
                padding: 24px
            }

            .dashboard-welcome h1 {
                font-size: 1.55rem
            }

            .dashboard-date {
                text-align: left;
                margin-top: 18px
            }
        }

        .dashboard-fab-container {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1030
        }

        .dashboard-fab-button {
            width: 52px;
            height: 52px;
            margin: 8px 0 0 8px;
            border: 0;
            border-radius: 50%;
            color: #fff;
            background: var(--dashboard-blue);
            box-shadow: 0 8px 20px rgba(36, 87, 214, .3)
        }

        .dashboard-template-fab {
            background: var(--dashboard-teal)
        }

        .dashboard-fab-button.is-open {
            transform: rotate(45deg)
        }

        .dashboard-fab-menu {
            display: none;
            position: absolute;
            right: 0;
            bottom: 68px;
            min-width: 180px
        }

        .dashboard-fab-menu.show {
            display: block
        }

        .dashboard-fab-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 6px 0;
            padding: 10px 14px;
            border-radius: 10px;
            color: var(--dashboard-ink);
            background: #fff;
            box-shadow: 0 6px 16px rgba(23, 35, 60, .16);
            white-space: nowrap
        }

        .dashboard-fab-item:hover {
            color: var(--dashboard-blue);
            text-decoration: none
        }

        .dashboard-filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
        }

        .dashboard-filter-bar .form-control {
            height: 40px;
            border-color: #dbe3f0;
            border-radius: 9px;
            box-shadow: none
        }

        .dashboard-filter-bar .form-control:focus {
            border-color: var(--dashboard-blue);
            box-shadow: 0 0 0 3px rgba(36, 87, 214, .12)
        }

        .dashboard-filter-bar #dashboard-record-type {
            width: 190px;
            flex: 0 0 190px
        }

        .dashboard-filter-bar #dashboard-record-search {
            min-width: 220px;
            flex: 1 1 auto
        }

        .dashboard-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dashboard-muted);
            pointer-events: none
        }

        .dashboard-search-wrap {
            position: relative;
            flex: 1 1 auto
        }

        .dashboard-search-wrap .form-control {
            padding-left: 38px
        }

        @media (max-width:767px) {
            .dashboard-filter-bar {
                flex-wrap: wrap
            }

            .dashboard-filter-bar #dashboard-record-type {
                width: 100%;
                flex: 1 1 100%
            }

            .dashboard-search-wrap {
                min-width: 100%
            }
        }
    </style>
@endsection

@section('page_script')
    @if (count($recordTypes) > 0)
    <script>
        $(function() {
            var table = $('#dashboard-records-table');
            // Only the header wording changes with the record type. DataTables 3
            // dropped `column().searchable()`, so the search columns are decided
            // by the endpoint instead: every column of the record type is
            // searchable, except the markup-only action column.
            var columns = {
                teachers: ['Teacher name', 'Email address'],
                students: ['Student name', 'LRN'],
                classrooms: ['Classroom name', 'Grade level']
            };
            var dataTable = table.DataTable({
                serverSide: true,
                processing: true,
                deferRender: true,
                ajax: {
                    url: '/dashboard/records',
                    data: function(data) {
                        data.type = $('#dashboard-record-type').val();
                    }
                },
                columns: [
                    { data: 'record' },
                    { data: 'detail' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']]
            });

            function applyRecordType(type) {
                var titles = columns[type];

                if (!titles) {
                    return;
                }

                dataTable.column(0).title(titles[0]);
                dataTable.column(1).title(titles[1]);
            }

            // The select already carries the record type the first request is
            // built from, so the initial pass only labels the header; every
            // later change clears the stale search and reloads.
            applyRecordType($('#dashboard-record-type').val());

            $('#dashboard-record-type').on('change', function() {
                $('#dashboard-record-search').val('');
                applyRecordType($(this).val());
                dataTable.search('').ajax.reload();
            });

            $('#dashboard-record-search').on('input', function() {
                dataTable.search($(this).val()).draw();
            });

            $('#dashboard-actions-button').on('click', function() {
                var menu = $('#dashboard-actions-menu');
                var isOpen = menu.hasClass('show');
                menu.toggleClass('show', !isOpen).attr('aria-hidden', isOpen);
                $(this).attr('aria-expanded', !isOpen).toggleClass('is-open', !isOpen);
            });
            $(document).on('click', function(event) {
                if (!$(event.target).closest('.dashboard-fab-container').length) {
                    $('#dashboard-actions-menu').removeClass('show').attr('aria-hidden', 'true');
                    $('#dashboard-actions-button').attr('aria-expanded', 'false').removeClass('is-open');
                }
            });
        });
    </script>
    @endif
@endsection

@section('content')
    @include('layouts.message')
    <div class="dashboard-shell pb-4">
        <section class="dashboard-welcome mb-4">
            <div class="row align-items-center position-relative" style="z-index:1">
                <div class="col-md-8 d-flex align-items-center">
                    <img class="dashboard-avatar mr-3 d-none d-sm-block" src="/images/{{ $details['gender'] }}.png"
                        alt="Profile image">
                    <div>
                        <div class="eyebrow">STARS / Daily briefing</div>
                        <h1>Welcome back, {{ $details['fullname'] }}.</h1>
                        <p class="mb-0">qoute:{{ $daily_quote }}�</p>
                    </div>
                </div>
                <div class="col-md-4 dashboard-date mt-3 mt-md-0"><span
                        class="eyebrow">{{ now()->format('l, F j, Y') }}</span><br><span>{{ $classification }}</span></div>
            </div>
        </section>
        <section class="row mb-4">
            <div class="col-lg-4 mb-3 mb-lg-0">
                <div class="dashboard-context-card academic p-3">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-context-icon mr-3"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <div class="dashboard-label dashboard-muted">Active academic year</div>
                            <div class="dashboard-context-value">
                                {{ $academic_year ? $academic_year->from . ' - ' . $academic_year->to : 'Not set' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="dashboard-context-card school p-3">
                    <div class="d-flex align-items-center">
                        <div class="dashboard-context-icon mr-3"><i class="fas fa-school"></i></div>
                        <div>
                            <div class="dashboard-label dashboard-muted">
                                {{ $details['level'] ? $details['level'] . ' context' : 'School details' }}</div>
                            <div class="dashboard-context-value">{{ $details['school'] ?? 'All schools' }}</div><small
                                class="dashboard-muted">
                                @if ($details['level'] == 'School')
                                    {{ $details['district'] }}, {{ $details['division'] }} � {{ $details['school_code'] }}
                                @elseif ($details['level'] == 'District')
                                    {{ $details['division'] }} � {{ $details['district'] }}
                                @elseif ($details['level'] == 'Division')
                                    {{ $details['division'] }}
                                @else
                                    System-wide access
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="row mb-4">
            @foreach ($statistics as $stat)
                <div class="col-6 col-lg-3 mb-3"><a class="dashboard-stat-card d-block p-3 text-decoration-none"
                        href="{{ $stat['url'] }}">
                        <div class="dashboard-stat-icon mb-3"><i class="{{ $stat['icon'] }}"></i></div>
                        <div class="dashboard-stat-value">{{ number_format($stat['value']) }}</div>
                        <div class="dashboard-muted small mt-2">{{ $stat['label'] }}</div>
                    </a></div>
            @endforeach
        </section>
        @if (count($recordTypes) > 0)
        <section class="dashboard-search-card mb-4">
            <div class="card-header d-flex flex-wrap gap-3">
                <div>
                    <h5 class="mb-1">School directory</h5>
                    <small class="dashboard-muted">Browse and search your current records.</small>
                </div>
                <div class="dashboard-filter-bar">
                    <label for="dashboard-record-type" class="sr-only">Record type</label>
                    <select id="dashboard-record-type" class="form-control">
                        @foreach (['teachers' => 'Teachers', 'students' => 'Students', 'classrooms' => 'Classrooms'] as $value => $label)
                            @if (in_array($value, $recordTypes, true))
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                    <div class="dashboard-search-wrap">
                        <i class="fas fa-search dashboard-search-icon"></i>
                        <label for="dashboard-record-search" class="sr-only">Search records</label>
                        <input id="dashboard-record-search" class="form-control" type="search"
                            placeholder="Search by name, email, LRN, or classroom">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dashboard-records-table" class="table table-bordered mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>Record</th>
                                <th>Detail</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
        @endif
        @if ($classification != 'Student')
            <div class="modal fade" id="dashboard-templates-modal" tabindex="-1"
                aria-labelledby="dashboard-templates-title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="dashboard-templates-title" class="modal-title">Downloadable templates</h5><button
                                type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                @foreach ($templates as $code => $template)
                                    @if (($code == 'SHU' || $code == 'CU') && $classification == 'School Head')
                                        @continue
                                    @endif
                                    @if (($classification == 'Teacher' && $code != 'ANS-KEY') || ($classification != 'Teacher' && $code == 'ANS-KEY'))
                                        @continue
                                    @endif
                                    <div class="col-md-6 mb-2"><a class="dashboard-template-link"
                                            href="/download_template/{{ $code }}"><span><i
                                                    class="far fa-file-alt mr-2 text-primary"></i>{{ $template }}</span><i
                                                class="fas fa-download text-success"></i></a></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="dashboard-fab-container">
            <div id="dashboard-actions-menu" class="dashboard-fab-menu" aria-hidden="true">
                <a class="dashboard-fab-item" href="/students/create"><i class="fas fa-user-graduate"></i><span>Add
                        student</span></a>
                <a class="dashboard-fab-item" href="/teachers/create"><i class="fas fa-user-tie"></i><span>Add
                        teacher</span></a>
                <a class="dashboard-fab-item" href="/sections"><i class="fas fa-layer-group"></i><span>Add
                        section</span></a>
            </div>
            <button type="button" id="dashboard-actions-button" class="dashboard-fab-button" aria-expanded="false"
                aria-controls="dashboard-actions-menu" aria-label="Add a new record"><i class="fas fa-plus"></i></button>
            @if ($classification != 'Student')
                <button type="button" class="dashboard-fab-button dashboard-template-fab" data-toggle="modal"
                    data-target="#dashboard-templates-modal" aria-label="Download templates"><i
                        class="fas fa-file-download"></i></button>
            @endif
        </div>
    </div>
@endsection
