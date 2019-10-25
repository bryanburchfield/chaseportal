<nav id="sidebar" class="active">
    <div class="sidebar-header">
        <h3><img class="img-responsive" src="/img/ChaseData-Transparent-Large2.png" alt=""></h3>
        <strong><img src="/img/ChaseData-Transparent-Large2.png" class="img-responsive mb_logo"></strong>
    </div>

    <ul class="list-unstyled components">
        <li <?php echo $page['menuitem'] == 'admindash' ? 'class="active"' : ''; ?>><a href="{{ action('MasterDashController@adminDashboard') }}"><i class="fas fa-sign-in-alt"></i>Admin Inbound</a></li>
        <li <?php echo $page['menuitem'] == 'adminoutbounddash' ? 'class="active"' : ''; ?>><a href="{{ action('MasterDashController@adminOutboundDashboard') }}"><i class="fas fa-sign-out-alt"></i>Admin Outbound</a></li>

        @if(Auth::user()->email != 'btmarketing@chasedatacorp.com')
          <li {!! $page['menuitem'] == 'trenddash' ? 'class="active"' : ''!!}><a href="{{ action('MasterDashController@trendDashboard') }}"><i class="fas fa-chart-area"></i>Trend Dashboard</a></li>
        @endif

        <li {!! $page['menuitem'] == 'leaderdash' ? 'class="active"' : ''!!}><a href="{{ action('MasterDashController@leaderDashboard') }}"><i class="fas fa-trophy"></i>Leadboard</a></li>
        <li {!! $page['menuitem'] == 'kpidash' ? 'class="active"' : ''!!}><a href="{{ action('MasterDashController@kpi') }}"><i class="fas fa-paper-plane"></i>KPIs</a></li>
        <li {!! $page['menuitem'] == 'reports' ? 'class="active"' : ''!!} data-toggle="modal" data-target="#reports_modal"><a href="#"><i class="fas fa-file-contract"></i>Reports</a></li>

        @if (config('app.env') != 'production')
            <li {!! $page['menuitem'] == 'tools' ? 'class="active"' : ''!!} ><a href="{{ action('LeadsController@rules') }}"><i class="fas fa-tools"></i>Tools</a></li>
        @endif

        @can('accessAdmin')
            <li {!! $page['menuitem'] == 'admin' ? 'class="active"' : ''!!}><a href="{{ action('Admin@index') }}"><i class="fas fa-user-cog"></i>Admin</a></li>
        @endcan

        @cannot('accessAdmin')
            <li {!! $page['menuitem'] == 'settings' ? 'class="active"' : ''!!}><a href="{{ action('MasterDashController@showSettings') }}"><i class="fas fa-user-cog"></i>Settings</a></li>
        @endcannot
    </ul>
</nav>