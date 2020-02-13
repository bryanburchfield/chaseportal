<nav id="sidebar" class="active">
    <div class="sidebar-header">
        <h3><img class="img-responsive" src="/img/ChaseData-Transparent-Large2.png" alt=""></h3>
        <strong><img src="/img/ChaseData-Transparent-Large2.png" class="img-responsive mb_logo"></strong>
    </div>

    <ul class="list-unstyled components">
        <li><a class="back_to_sidenav" href="#"><i class="fas fa-arrow-circle-left"></i>Go Back</a></li>
        <li <?php echo !empty($page['menuitem']) && $page['menuitem']== 'admindurationdash' ? 'class="active"' : ''; ?>><a href="{{action('MasterDashController@adminDurationDashboard')}}"><i class="fas fa-user-clock"></i>Duration Dashboard</a></li>
        <li <?php echo !empty($page['menuitem']) && $page['menuitem']== 'admindistinctagentdash' ? 'class="active"' : ''; ?>><a href="{{action('MasterDashController@adminDistinctAgentDashboard')}}"><i class="fas fa-user-check"></i>Distinct Agent</a></li>
        <li <?php echo !empty($page['menuitem']) && $page['menuitem']== 'manage_clients' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@manageClients') }}"><i class="fas fa-users"></i>Manage Clients</a></li>
        <li <?php echo !empty($page['menuitem']) && $page['menuitem']== 'cdr_lookup' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@cdrLookup') }}"><i class="fas fa-search"></i> CDR Lookup</a></li>
        @can('accessSuperAdmin')
            <li <?php echo !empty($page['menuitem']) && $page['menuitem']== 'webhook_generator' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@webhookGenerator') }}"><i class="fas fa-link"></i> Webhook Generator</a></li>
        @endcan
        <li <?php echo !empty($page['menuitem']) && $page['menuitem']== 'settings' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@settings') }}"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
</nav>
