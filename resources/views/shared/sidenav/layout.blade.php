<nav id="sidebar" class="active">
    <div class="sidebar-header">
        <h3><img class="img-fluid" src="/img/ChaseData-Transparent-Large2.png" alt=""></h3>
        <strong><img src="/img/ChaseData-Transparent-Large2.png" class="img-fluid mb_logo"></strong>
    </div>

    <ul class="list-unstyled components">
        <input type="hidden" class="page_menuitem" value="@isset($page['menuitem']){{$page['menuitem']}}@endisset">
        @yield('sidenav')
    </ul>
</nav>