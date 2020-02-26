<nav id="sidebar" class="active">
    <div class="sidebar-header">
        <h3><img class="img-responsive" src="/img/ChaseData-Transparent-Large2.png" alt=""></h3>
        <strong><img src="/img/ChaseData-Transparent-Large2.png" class="img-responsive mb_logo"></strong>
    </div>

    <ul class="list-unstyled components">
        <input type="hidden" class="page_menuitem" value="<?php echo !empty($page['menuitem']) ? $page['menuitem'] : '';?>">
        @can('accessSuperAdmin')
        <li><a class="back_to_sidenav" href="#"><i class="fas fa-arrow-circle-left"></i>{{__('widgets.go_back')}}</a></li>
        <li data-page="admindurationdash" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'admindurationdash' ? 'class="active"' : ''; ?>><a href="{{action('MasterDashController@adminDurationDashboard')}}"><i class="fas fa-user-clock"></i>{{__('sidenav.duration_dashboard')}}</a></li>
        @endcan
        <li data-page="admindistinctagentdash" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'admindistinctagentdash' ? 'class="active"' : ''; ?>><a href="{{action('MasterDashController@adminDistinctAgentDashboard')}}"><i class="fas fa-user-check"></i>{{__('sidenav.distinct_agent')}}</a></li>
        <li data-page="manage_users" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'manage_users' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@manageUsers') }}"><i class="fas fa-users"></i>{{__('sidenav.manage_users')}}</a></li>
        <li data-page="cdr_lookup" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'cdr_lookup' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@cdrLookup') }}"><i class="fas fa-search"></i> {{__('sidenav.cdr_lookup')}}</a></li>

        @can('accessSuperAdmin')
            <li data-page="webhook_generator" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'webhook_generator' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@webhookGenerator') }}"><i class="fas fa-link"></i> Webhook Generator</a></li>
            <li data-page="settings" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'settings' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@settings') }}"><i class="fas fa-link"></i> {{__('widgets.edit_myself')}}</a></li>
        @endcan
        </ul>
</nav>
