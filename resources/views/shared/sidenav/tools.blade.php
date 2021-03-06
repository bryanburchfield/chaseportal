@extends('shared.sidenav.layout')
@section('sidenav')
    <li><a class="update_nav_link" data-path="main" href="#"><i class="fas fa-arrow-circle-left"></i>{{__('widgets.go_back')}}</a></li>

    @can('accessAdmin')
        <li data-page="dnc_importer" @php echo !empty($page['menuitem']) && $page['menuitem']== 'dnc_importer' ? 'class="active"' : ''; @endphp><a href="{{url('/tools/dnc_importer')}}"><i class="fas fa-user-check"></i>DNC Importer</a></li>
    @endcan

    @can('accessSuperAdmin')
        <li data-page="webhook_generator" @php echo !empty($page['menuitem']) && $page['menuitem']== 'webhook_generator' ? 'class="active"' : ''; @endphp><a href="{{ action('AdminController@webhookGenerator') }}"><i class="fas fa-link"></i> Webhook Generator</a></li>
        <li data-page="cdr_lookup" @php echo !empty($page['menuitem']) && $page['menuitem']== 'cdr_lookup' ? 'class="active"' : ''; @endphp><a href="{{ action('AdminController@cdrLookup') }}"><i class="fas fa-search"></i> {{__('sidenav.cdr_lookup')}}</a></li>
        <li data-page="accounting_reports" @php echo !empty($page['menuitem']) && $page['menuitem']== 'accounting_reports' ? 'class="active"' : ''; @endphp><a href="{{ action('AdminController@accountingReports') }}"><i class="fas fa-file-invoice"></i> {{__('sidenav.accounting_reports')}}</a></li>
    @endcan
@endsection
