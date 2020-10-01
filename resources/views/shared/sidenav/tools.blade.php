@extends('shared.sidenav.layout')
@section('sidenav')
    <li><a class="update_nav_link" data-path="main" href="#"><i class="fas fa-arrow-circle-left"></i>{{__('widgets.go_back')}}</a></li>

    @can('accessAdmin')
        <li data-page="dnc_importer" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'dnc_importer' ? 'class="active"' : ''; ?>><a href="{{url('/tools/dnc_importer')}}"><i class="fas fa-user-check"></i>DNC Importer</a></li>
          @if(!empty(Auth::user()->dialer->status_url))
            <li data-page="tools" <?@php echo !empty($page['menuitem']) && $page['menuitem'] == 'server_status' ? 'class="active"' : ''@endphp ><a href="{{ Auth::user()->dialer->status_url }}" target="_blank"><i class="fas fa-server"></i>{{__('sidenav.server_status')}}</a></li>
        @endif
    @endcan

    @can('accessSuperAdmin')
        <li data-page="webhook_generator" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'webhook_generator' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@webhookGenerator') }}"><i class="fas fa-link"></i> Webhook Generator</a></li>
        <li data-page="cdr_lookup" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'cdr_lookup' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@cdrLookup') }}"><i class="fas fa-search"></i> {{__('sidenav.cdr_lookup')}}</a></li>
    @endcan
@endsection
