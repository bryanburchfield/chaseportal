@extends('shared.sidenav.layout')
@section('sidenav')
    <li data-page="dashboards" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'dashboards' ? 'class="active"' : ''?>><a href="#" class="update_nav_link" data-path="dashboards"><i class="fas fa-chart-area"></i>{{__('sidenav.dashboards')}}</a></li>
    <li data-page="leaderdash" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'leaderdash' ? 'class="active"' : ''?>><a href="{{ action('MasterDashController@leaderDashboard') }}"><i class="fas fa-trophy"></i>{{__('sidenav.leaderboard')}}</a></li>
    <li data-page="kpidash" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'kpidash' ? 'class="active"' : ''?>><a href="{{ action('MasterDashController@kpi') }}"><i class="fas fa-paper-plane"></i>{{__('sidenav.kpis')}}</a></li>
    <li data-page="reports" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'reports' ? 'class="active"' : ''?> data-toggle="modal" data-target="#reports_modal"><a href="#"><i class="fas fa-file-contract"></i>{{__('sidenav.reports')}}</a></li>

    @can('accessAdmin')
        <li data-page="playbook" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'playbook' ? 'class="active"' : ''; ?>><a href="{{action('PlaybookController@index')}}"><i class="fas fa-book"></i>Contact Playbook</a></li>
    @endif

    @can('accessSuperAdmin')
        <li><a href="https://contactflow.chasedatacorp.com/" target="_blank"><i class="fas fa-pencil-ruler"></i> Flow Builder</a></li>
    @endcan

    @can('accessAdmin')
        <li data-page="tools" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'tools' ? 'class="active"' : ''?> ><a href="#" class="update_nav_link" data-path="tools"><i class="fas fa-tools"></i>{{__('sidenav.tools')}}</a></li>
    @endif

    <li data-page="settings" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'settings' ? 'class="active"' : ''?>><a href="{{ action('MasterDashController@showSettings') }}"><i class="fas fa-cog"></i>{{__('sidenav.settings')}}</a></li>

    @can('accessAdmin')
        <li data-page="admin" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'admin' ? 'class="active"' : ''?>><a href="#" class="update_nav_link" data-path="admin"><i class="fas fa-user-shield"></i>{{__('sidenav.admin')}}</a></li>
    @endcan
@endsection