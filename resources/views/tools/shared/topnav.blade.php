<ul class="nav nav-tabs">
	@if ( Auth::user()->isType(['admin', 'superadmin']) || Auth::user()->group_id == 777)
    <li class="nav-item"><a class="nav-link @if ($toolpage == 'rules') active @endif"  href="{{url('/tools/contactflow_builder')}}">{{__('tools.contact_flowbuilder')}}</a></li>
    @endif
    <li class="nav-item"><a class="nav-link @if ($toolpage == 'dnc') active @endif" href="{{url('/tools/dnc_importer')}}">{{__('tools.dnc_importer')}}</a></li>
    <li class="nav-item"><a class="nav-link @if ($toolpage == 'email_drip') active @endif" href="{{action('EmailDripController@index')}}">{{__('tools.email_drip_builder')}}</a></li>
</ul>
