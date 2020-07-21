@if (isset($page['sidenav']))
<<<<<<< HEAD
    @switch ($page['sidenav'])
        @case ('dashboards')
            @include('shared.dashboard_sidenav')
            @break
        @case ('admin')
            @include('shared.admin_sidenav')
            @break
        @case ('tools')
            @include('shared.tools_sidenav')
            @break
        @default
            @include('shared.default_sidenav')
    @endswitch
@else
    @include('shared.sidenav.' . $page['sidenav'])
@else
    @include('shared.sidenav.main')
@endif