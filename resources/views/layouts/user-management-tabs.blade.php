@php
    $userTabs = App\Support\UserManagementTab::availableFor(Auth::user());
    $currentUserTab = App\Support\UserManagementTab::current();
@endphp

@if (count($userTabs) > 1)
    <ul class="nav nav-tabs mb-3">
        @foreach ($userTabs as $userTab)
            <li class="nav-item">
                <a href="{{ $userTab->url() }}"
                    class="nav-link {{ $currentUserTab === $userTab ? 'active' : '' }}">
                    <i class="fa {{ $userTab->icon() }} mr-2"></i> {{ $userTab->label() }}
                </a>
            </li>
        @endforeach
    </ul>
@endif
