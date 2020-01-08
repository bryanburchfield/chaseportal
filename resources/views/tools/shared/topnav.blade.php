<ul class="nav nav-tabs">
    <li @if ($toolpage == 'rules') class="active" @endif><a href="{{url('/tools/contactflow_builder')}}">{{__('tools.contact_flowbuilder')}}</a></li>
    <li @if ($toolpage == 'dnc') class="active" @endif><a href="{{url('/tools/dnc_importer')}}">{{__('tools.dnc_importer')}}</a></li>
</ul>
