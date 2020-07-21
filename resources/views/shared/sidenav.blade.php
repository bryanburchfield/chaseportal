@if (isset($page['sidenav']))
    @switch ($page['sidenav'])
        @case ('admin')
            @include('shared.sidenav.admin')
            @break
        @case ('tools')
            @include('shared.sidenav.tools')
            @break
        @case ('dashboards')
            @include('shared.sidenav.dashboards')
            @break
        @default
            @include('shared.sidenav.main')
    @endswitch
@else
    @include('shared.sidenav.main')
@endif