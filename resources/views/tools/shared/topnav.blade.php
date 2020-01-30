<ul class="nav nav-tabs">
	@if ( Auth::user()->isType('admin') || Auth::user()->group_id == 777)
    <li @if ($toolpage == 'rules') class="active" @endif><a href="{{url('/tools/contactflow_builder')}}">{{__('tools.contact_flowbuilder')}}</a></li>
    @endif
    <li @if ($toolpage == 'dnc') class="active" @endif><a href="{{url('/tools/dnc_importer')}}">{{__('tools.dnc_importer')}}</a></li>
</ul>
