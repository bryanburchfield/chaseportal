<ul class="nav nav-tabs">
    <li @if ($toolpage == 'rules') class="active" @endif><a href="{{url('/tools/contactflow_builder')}}">{{__('tools.contact_flowbuilder')}}</a></li>
    <li @if ($toolpage == 'dnc') class="active" @endif><a href="{{url('/tools/dnc_importer')}}">{{__('tools.dnc_importer')}}</a></li>

    <li @if ($toolpage == 'email_drip') class="active" @endif><a href="{{url('/tools/email_drip_builder')}}">{{__('tools.email_drip_builder')}}</a></li>
</ul>
