@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_css')
    <style>
        .ecdc-dashboard {
            --ecdc-ink: #17324d;
            --ecdc-muted: #6b7d8f;
            --ecdc-teal: #148a82;
            --ecdc-purple: #6a0572;
            --ecdc-teal-soft: #e7f5f3;
            --ecdc-gold: #d69c2f;
            --ecdc-gold-soft: #fff5dc;
            --ecdc-border: #dce6ed;
            position: relative;
            padding: 8px 0 28px;
        }

        .ecdc-hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
            padding: 30px 32px;
            border: 1px solid var(--ecdc-border);
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(23, 50, 77, .08);
        }

        .ecdc-hero::after {
            position: absolute;
            right: -55px;
            bottom: -85px;
            width: 230px;
            height: 230px;
            border-radius: 46% 54% 70% 30%;
            background: var(--ecdc-teal-soft);
            content: "";
            transform: rotate(20deg);
        }

        .ecdc-eyebrow {
            display: inline-flex;
            align-items: center;
            margin-bottom: 12px;
            color: var(--ecdc-teal);
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .ecdc-eyebrow::before {
            width: 28px;
            height: 2px;
            margin-right: 10px;
            background: var(--ecdc-gold);
            content: "";
        }

        .ecdc-title {
            margin: 0;
            color: var(--ecdc-ink);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(2rem, 4vw, 3.15rem);
            font-weight: 700;
            letter-spacing: -.04em;
            line-height: 1;
        }

        .ecdc-subtitle {
            max-width: 600px;
            margin: 14px 0 0;
            color: var(--ecdc-muted);
            font-size: 1rem;
            line-height: 1.65;
        }

        .ecdc-year {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            padding: 11px 16px;
            border: 1px solid rgba(20, 138, 130, .22);
            border-radius: 999px;
            background: rgba(255, 255, 255, .84);
            color: var(--ecdc-ink);
            font-size: .84rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .ecdc-year i {
            margin-right: 8px;
            color: var(--ecdc-teal);
        }

        .ecdc-overview {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 24px;
        }

        .ecdc-overview-item {
            position: relative;
            z-index: 1;
            padding: 16px 18px;
            border-left: 3px solid var(--ecdc-teal);
            border-radius: 4px 14px 14px 4px;
            background: #f7fafc;
        }

        .ecdc-overview-value {
            display: block;
            color: var(--ecdc-ink);
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1;
        }

        .ecdc-overview-label {
            display: block;
            margin-top: 7px;
            color: var(--ecdc-muted);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .ecdc-section-heading {
            margin: 0 2px 15px;
        }

        .ecdc-section-heading h2 {
            margin: 0;
            color: var(--ecdc-ink);
            font-size: 1.1rem;
            font-weight: 800;
        }

        .ecdc-section-heading p {
            margin: 4px 0 0;
            color: var(--ecdc-muted);
            font-size: .86rem;
        }

        .ecdc-classroom-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .ecdc-classroom {
            position: relative;
            overflow: hidden;
            min-height: 250px;
            border: 1px solid var(--ecdc-border);
            border-radius: 19px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(23, 50, 77, .06);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            animation: ecdc-card-enter .45s ease both;
            animation-delay: calc(var(--card-index) * 70ms);
        }

        .ecdc-classroom:hover {
            border-color: rgba(20, 138, 130, .45);
            box-shadow: 0 18px 36px rgba(23, 50, 77, .12);
            transform: translateY(-4px);
        }

        .ecdc-classroom-accent {
            height: 6px;
            background: linear-gradient(90deg, var(--ecdc-teal) 0 70%, var(--ecdc-purple) 70%);
        }

        .ecdc-classroom-body {
            padding: 22px;
        }

        .ecdc-classroom-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .ecdc-classroom-kicker {
            display: block;
            margin-bottom: 5px;
            color: var(--ecdc-muted);
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .ecdc-classroom-name {
            margin: 0;
            color: var(--ecdc-ink);
            font-size: 1.15rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .ecdc-classroom-number {
            color: rgba(23, 50, 77, .1);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 2.35rem;
            font-weight: 700;
            line-height: .9;
        }

        .ecdc-periods {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .ecdc-period {
            padding: 14px;
            border-radius: 14px;
        }

        .ecdc-period--bosy {
            background: var(--ecdc-teal-soft);
            color: var(--ecdc-teal);
        }

        .ecdc-period--eosy {
            background: var(--ecdc-gold-soft);
            color: #9b6b0d;
        }

        .ecdc-period strong {
            display: block;
            color: var(--ecdc-ink);
            font-size: 1.7rem;
            line-height: 1;
        }

        .ecdc-period span {
            display: block;
            margin-top: 6px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .09em;
        }

        .ecdc-classroom-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #edf2f5;
            color: var(--ecdc-ink);
            font-size: .84rem;
            font-weight: 800;
            text-decoration: none;
        }

        .ecdc-classroom-action:hover {
            color: var(--ecdc-teal);
            text-decoration: none;
        }

        .ecdc-classroom-action i {
            display: inline-grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border-radius: 50%;
            background: var(--ecdc-ink);
            color: #fff;
            transition: background .2s ease, transform .2s ease;
        }

        .ecdc-classroom-action:hover i {
            background: var(--ecdc-teal);
            transform: translateX(3px);
        }

        .ecdc-empty {
            padding: 55px 24px;
            border: 1px dashed #b9cad5;
            border-radius: 18px;
            background: #f8fbfc;
            color: var(--ecdc-muted);
            text-align: center;
        }

        .ecdc-empty i {
            display: block;
            margin-bottom: 12px;
            color: var(--ecdc-teal);
            font-size: 2rem;
        }

        @keyframes ecdc-card-enter {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 991.98px) {
            .ecdc-classroom-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .ecdc-hero {
                padding: 24px 20px;
                border-radius: 17px;
            }

            .ecdc-year {
                margin-top: 18px;
            }

            .ecdc-overview,
            .ecdc-classroom-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .ecdc-classroom {
                animation: none;
                transition: none;
            }
        }
    </style>
@endsection

@section('content')
    @include('layouts.assessment-tabs')
    @include('layouts.message')

    @php
        $kinderClassrooms = collect($classrooms['Kinder'] ?? []);
    @endphp

    <div class="container-fluid ecdc-dashboard">
        <div class="ecdc-section-heading">
            <h2>Your Kinder classrooms</h2>
            <p>Select a classroom to view uploaded assessments and student ECDC records.</p>
        </div>

        @if ($kinderClassrooms->isNotEmpty())
            <div class="ecdc-classroom-grid">
                @foreach ($kinderClassrooms as $classroom)
                    <article class="ecdc-classroom" style="--card-index: {{ $loop->index }}">
                        <div class="ecdc-classroom-accent"></div>
                        <div class="ecdc-classroom-body">
                            <div class="ecdc-classroom-top">
                                <div>
                                    <span class="ecdc-classroom-kicker">Kinder section</span>
                                    <h3 class="ecdc-classroom-name">{{ $classroom['section'] }}</h3>
                                </div>
                                <span aria-hidden="true">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>

                            <div class="ecdc-periods">
                                <div class="badge bg-teal ecdc-period ecdc-period--bosy">
                                    <strong>{{ $classroom['details']['bosy'] }}</strong>
                                    <span>BOSY</span>
                                </div>
                                <div class="badge bg-purple ecdc-period ecdc-period--eosy">
                                    <strong>{{ $classroom['details']['eosy'] }}</strong>
                                    <span>EOSY</span>
                                </div>
                            </div>

                            <a href="/ecdcs/classroom/{{ $classroom['classroom_id'] }}"
                                class="ecdc-classroom-action"
                                aria-label="Open Kinder {{ $classroom['section'] }} ECDC records">
                                <span>Open classroom records</span>
                                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="ecdc-empty">
                <i class="fas fa-school" aria-hidden="true"></i>
                <strong>No Kinder classrooms available</strong>
                <div>Assigned Kinder classrooms will appear here.</div>
            </div>
        @endif
    </div>
@endsection
