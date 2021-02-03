@extends('shared.sidenav.layout')
@section('sidenav')
    <li><a class="update_nav_link" data-path="main" href="#"><i class="fas fa-arrow-circle-left"></i>{{__('widgets.go_back')}}</a></li>

    @can('accessSuperAdmin')
        <li data-page="admindurationdash" @php echo !empty($page['menuitem']) && $page['menuitem']== 'admindurationdash' ? 'class="active"' : ''; @endphp><a href="{{action('MasterDashController@adminDurationDashboard')}}"><i class="fas fa-user-clock"></i>{{__('sidenav.duration_dashboard')}}</a></li>
        <li data-page="admindistinctagentdash" @php echo !empty($page['menuitem']) && $page['menuitem']== 'admindistinctagentdash' ? 'class="active"' : ''; @endphp><a href="{{action('MasterDashController@adminDistinctAgentDashboard')}}"><i class="fas fa-user-check"></i>{{__('sidenav.distinct_agent')}}</a></li>
    @endcan

    @can('accessAdmin')
        <li data-page="compliancedash" @php echo !empty($page['menuitem']) && $page['menuitem'] == 'compliancedash' ? 'class="active"' : ''@endphp><a href="{{ action('MasterDashController@complianceDashboard') }}"><i class="fas fa-clipboard-check"></i>{{__('sidenav.compliance_dashboard')}}</a></li>
        <li data-page="manage_users" @php echo !empty($page['menuitem']) && $page['menuitem']== 'manage_users' ? 'class="active"' : ''; @endphp><a href="{{ action('AdminController@manageUsers') }}"><i class="fas fa-users"></i>{{__('sidenav.manage_users')}}</a></li>
    @endcan

    @can('accessSuperAdmin')
        <li data-page="spam_check" @php echo !empty($page['menuitem']) && $page['menuitem']== 'spam_check' ? 'class="active"' : ''; @endphp><a href="{{ action('SpamCheckController@index') }}"><i class="fas fa-phone"></i> Spam Check</a></li>
        <li data-page="settings" @php echo !empty($page['menuitem']) && $page['menuitem']== 'settings' ? 'class="active"' : ''; @endphp><a href="{{ action('AdminController@settings') }}"><i class="fas fa-user-edit"></i> Edit Myself</a></li>
        <li data-page="notifications" @php echo !empty($page['menuitem']) && $page['menuitem']== 'notifications' ? 'class="active"' : ''; @endphp><a href="{{ action('FeatureMessageController@index') }}"><i class="fas fa-bell"></i> Notifications</a></li>
    @endcan
@endsection