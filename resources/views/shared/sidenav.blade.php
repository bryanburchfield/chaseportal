@if (isset($page['sidenav']))
    @include('shared.sidenav.' . $page['sidenav'])
@else
    @include('shared.sidenav.main')
@endif