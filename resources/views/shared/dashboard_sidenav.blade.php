<nav id="sidebar" class="active">
    <div class="sidebar-header">
        <h3><img class="img-responsive" src="/img/ChaseData-Transparent-Large2.png" alt=""></h3>
        <strong><img src="/img/ChaseData-Transparent-Large2.png" class="img-responsive mb_logo"></strong>
    </div>

    <ul class="list-unstyled components">
        <input type="hidden" class="page_menuitem" value="<?php echo !empty($page['menuitem']) ? $page['menuitem'] : '';?>">
        <li data-page="inbounddash" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'inbounddash' ? 'class="active"' : ''; ?>><a href="{{ action('MasterDashController@inboundDashboard') }}"><i class="fas fa-sign-in-alt"></i>{{__('sidenav.inbound')}}</a></li>
        <li data-page="outbounddash" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'outbounddash' ? 'class="active"' : ''; ?>><a href="{{ action('MasterDashController@outboundDashboard') }}"><i class="fas fa-sign-out-alt"></i>{{__('sidenav.outbound')}}</a></li>

        @if(Auth::user()->email != 'btmarketing@chasedatacorp.com')
          <li data-page="trenddash" <?php echo !empty($page['menuitem']) &&  $page['menuitem'] == 'trenddash' ? 'class="active"' : ''?>><a href="{{ action('MasterDashController@trendDashboard') }}"><i class="fas fa-chart-area"></i>{{__('sidenav.trend_dashboard')}}</a></li>
        @endif

        <li data-page="leaderdash" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'leaderdash' ? 'class="active"' : ''?>><a href="{{ action('MasterDashController@leaderDashboard') }}"><i class="fas fa-trophy"></i>{{__('sidenav.leaderboard')}}</a></li>
        
        @can('accessAdmin')
            <li data-page="playbook" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'playbook' ? 'class="active"' : ''; ?>><a href="{{action('PlaybookController@index')}}"><i class="fas fa-book"></i>Contacts Playbook</a></li>
        @endcan
        
        <li data-page="kpidash" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'kpidash' ? 'class="active"' : ''?>><a href="{{ action('MasterDashController@kpi') }}"><i class="fas fa-paper-plane"></i>{{__('sidenav.kpis')}}</a></li>
        <li data-page="reports" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'reports' ? 'class="active"' : ''?> data-toggle="modal" data-target="#reports_modal"><a href="#"><i class="fas fa-file-contract"></i>{{__('sidenav.reports')}}</a></li>

        @if (Auth::user()->isType(['admin','superadmin']))
            <li data-page="tools" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'tools' ? 'class="active"' : ''?> ><a href="#" class="tools_link" data-path="load_tools_nav"><i class="fas fa-tools"></i>{{__('sidenav.tools')}}</a></li>
        @endif

        <li data-page="settings" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'settings' ? 'class="active"' : ''?>><a href="{{ action('MasterDashController@showSettings') }}"><i class="fas fa-cog"></i>{{__('sidenav.settings')}}</a></li>

        @can('accessAdmin')
            <li data-page="admin" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'admin' ? 'class="active"' : ''?>><a href="#" class="admin_link" data-path="load_admin_nav"><i class="fas fa-user-shield"></i>{{__('sidenav.admin')}}</a></li>
        @endcan
    </ul>
</nav>
