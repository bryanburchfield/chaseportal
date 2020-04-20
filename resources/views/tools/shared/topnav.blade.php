<ul class="nav nav-tabs">
	@if ( Auth::user()->isType(['admin', 'superadmin']) || Auth::user()->group_id == 777)
    <li @if ($toolpage == 'rules') class="active" @endif><a href="{{url('/tools/contactflow_builder')}}">{{__('tools.contact_flowbuilder')}}</a></li>
    @endif
    <li @if ($toolpage == 'dnc') class="active" @endif><a href="{{url('/tools/dnc_importer')}}">{{__('tools.dnc_importer')}}</a></li>
    <li @if ($toolpage == 'email_drip') class="active" @endif><a href="{{action('EmailDripController@index')}}">{{__('tools.email_drip_builder')}}</a></li>
    <li @if ($toolpage == 'playbook') class="active" @endif><a href="{{action('PlaybookController@index')}}">{{__('tools.contacts_playbook')}}</a></li>
</ul>
