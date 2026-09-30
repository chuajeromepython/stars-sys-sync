@php
    $assessmentTabs = App\Support\AssessmentTab::availableFor(Auth::user());
    $currentTab = App\Support\AssessmentTab::current();
@endphp

@if (count($assessmentTabs))
    <ul class="nav nav-tabs mb-3">
        @foreach ($assessmentTabs as $assessmentTab)
            <li class="nav-item">
                <a href="{{ $assessmentTab->url() }}"
                    class="nav-link {{ $currentTab === $assessmentTab ? 'active' : '' }}">
                    {{ $assessmentTab->label() }}
                </a>
            </li>
        @endforeach
    </ul>
@endif
