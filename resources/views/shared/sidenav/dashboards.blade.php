@extends('shared.sidenav.layout')
@section('sidenav')
    <li><a class="update_nav_link" data-path="main" href="#"><i class="fas fa-arrow-circle-left"></i>{{__('widgets.go_back')}}</a></li>

    <li data-page="inbounddash" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'inbounddash' ? 'class="active"' : ''; ?>><a href="{{ action('MasterDashController@inboundDashboard') }}"><i class="fas fa-sign-in-alt"></i>{{__('sidenav.inbound')}}</a></li>
    <li data-page="outbounddash" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'outbounddash' ? 'class="active"' : ''; ?>><a href="{{ action('MasterDashController@outboundDashboard') }}"><i class="fas fa-sign-out-alt"></i>{{__('sidenav.outbound')}}</a></li>

	@can('accessAdmin')
	    <li data-page="realtimedash" <?php echo !empty($page['menuitem']) && $page['menuitem'] == 'realtimedash' ? 'class="active"' : ''?>><a href="{{ url('dashboards/realtimeagentdashboard') }}"><i class="fas fa-file-medical-alt"></i>{{__('sidenav.realtimedash')}}</a></li>
	@endif

    @if(Auth::user()->email != 'btmarketing@chasedatacorp.com')
        <li data-page="trenddash" <?php echo !empty($page['menuitem']) &&  $page['menuitem'] == 'trenddash' ? 'class="active"' : ''?>><a href="{{ action('MasterDashController@trendDashboard') }}"><i class="fas fa-chart-area"></i>{{__('sidenav.trend_dashboard')}}</a></li>
    @endif
@endsection()
