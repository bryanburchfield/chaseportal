@if (isset($page['sidenav']))
    @switch ($page['sidenav'])
        @case ('admin')
            @include('shared.admin_sidenav')
            @break
        @case ('tools')
            @include('shared.tools_sidenav')
            @break
        @default
            @include('shared.dashboard_sidenav')
    @endswitch
@else
    @include('shared.dashboard_sidenav')
@endif