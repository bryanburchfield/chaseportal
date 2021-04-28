@extends('shared.sidenav.layout')
@section('sidenav')
    <li><a class="update_nav_link" data-path="main" href="#"><i class="fas fa-arrow-circle-left"></i>{{__('widgets.go_back')}}</a></li>

    {{-- <li data-page="lead_detail" @php echo !empty($page['menuitem']) && $page['menuitem']== 'lead_detail' ? 'class="active"' : ''; @endphp><a href="{{action('LeadsController@leadDetail')}}"><i class="fas fa-file-alt"></i>{{__('tools.lead_detail')}}</a></li> --}}

    @can('accessAdmin')
        <li data-page="dnc_importer" @php echo !empty($page['menuitem']) && $page['menuitem']== 'dnc_importer' ? 'class="active"' : ''; @endphp><a href="{{action('DncController@index')}}"><i class="fas fa-user-check"></i>DNC Importer</a></li>

        @if(!empty(Auth::user()->dialer->status_url))
            <li data-page="tools" @php echo !empty($page['menuitem']) && $page['menuitem'] == 'server_status' ? 'class="active"' : ''@endphp ><a href="{{ action('DialerController@index') }}"><i class="fas fa-server"></i>{{__('sidenav.server_status')}}</a></li>
        @endif

        <li data-page="portal_form_builder" @php echo !empty($page['menuitem']) && $page['menuitem']== 'portal_form_builder' ? 'class="active"' : ''; @endphp><a href="{{ action('FormBuilderController@portalFormBuilder') }}"><i class="fas fa-wrench"></i>
            @can('accessSuperAdmin')
               Client Form Builder
            @else
                 {{__('sidenav.form_builder')}}
            @endcan
        </a></li>

    @endcan

    @can('accessSuperAdmin')
        <li data-page="form_builder" @php echo !empty($page['menuitem']) && $page['menuitem']== 'form_builder' ? 'class="active"' : ''; @endphp><a href="{{ action('AdminController@formBuilder') }}"><i class="fas fa-wrench"></i> {{__('sidenav.form_builder')}}</a></li>
        <li data-page="webhook_generator" @php echo !empty($page['menuitem']) && $page['menuitem']== 'webhook_generator' ? 'class="active"' : ''; @endphp><a href="{{ action('AdminController@webhookGenerator') }}"><i class="fas fa-link"></i> Webhook Generator</a></li>
        <li data-page="cdr_lookup" @php echo !empty($page['menuitem']) && $page['menuitem']== 'cdr_lookup' ? 'class="active"' : ''; @endphp><a href="{{ action('AdminController@cdrLookup') }}"><i class="fas fa-search"></i> {{__('sidenav.cdr_lookup')}}</a></li>
        <li data-page="accounting_reports" @php echo !empty($page['menuitem']) && $page['menuitem']== 'accounting_reports' ? 'class="active"' : ''; @endphp><a href="{{ action('AdminController@accountingReports') }}"><i class="fas fa-file-invoice"></i> {{__('sidenav.accounting_reports')}}</a></li>
    @endcan
@endsection
