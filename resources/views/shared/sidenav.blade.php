<nav id="sidebar" class="active">
    <div class="sidebar-header">
        <h3><img class="img-responsive" src="/img/ChaseData-Transparent-Large2.png" alt=""></h3>
        <strong><img src="/img/ChaseData-Transparent-Large2.png" class="img-responsive mb_logo"></strong>
    </div>

    <ul class="list-unstyled components">
        <li <?php echo $page['menuitem'] == 'admindash' ? 'class="active"' : ''; ?>><a class="dash" href="admindash"><i class="fas fa-sign-in-alt"></i>Admin Inbound</a></li>
        <li <?php echo $page['menuitem'] == 'adminoutbounddash' ? 'class="active"' : ''; ?>><a class="dash" href="adminoutbounddash"><i class="fas fa-sign-out-alt"></i>Admin Outbound</a></li>
        
        <?php 
          if(Auth::user()->email != 'btmarketing@chasedatacorp.com'){
        ?>
          <li {!! $page['menuitem'] == 'trenddash' ? 'class="active"' : ''!!}><a class="dash" href="trenddash"><i class="fas fa-chart-area"></i>Trend Dashboard</a></li>
        <?php
          }
        ?>
        
        <li {!! $page['menuitem'] == 'leaderdash' ? 'class="active"' : ''!!}><a class="dash" href="leaderdash"><i class="fas fa-trophy"></i>Leadboard</a></li>
        <li {!! $page['menuitem'] == 'kpidash' ? 'class="active"' : ''!!}><a class="dash" href="kpidash"><i class="fas fa-paper-plane"></i>KPIs</a></li>
        <li {!! $page['menuitem'] == 'reports' ? 'class="active"' : ''!!} data-toggle="modal" data-target="#reports_modal"><a class="reports_modal_nav" href="#"><i class="fas fa-file-contract"></i>Reports</a></li>
        <li {!! $page['menuitem'] == 'tools' ? 'class="active"' : ''!!} ><a href="{{ url('dashboards/tools') }}"><i class="fas fa-tools"></i>Tools</a></li>


        @can('accessAdmin')
            <li {!! $page['menuitem'] == 'admin' ? 'class="active"' : ''!!}><a href="{{ url('dashboards/admin') }}"><i class="fas fa-user-cog"></i>Admin</a></li>
        @endcan

        @cannot('accessAdmin')
            <li {!! $page['menuitem'] == 'settings' ? 'class="active"' : ''!!}><a href="{{ url('dashboards/settings') }}"><i class="fas fa-user-cog"></i>Settings</a></li>
        @endcannot
    </ul>
</nav>