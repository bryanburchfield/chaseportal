<ul class="nav nav-tabs">
    <li @if ($toolpage == 'rules') class="active" @endif><a href="{{action('LeadsController@index')}}">{{__('tools.contact_flowbuilder')}}</a></li>
    <li @if ($toolpage == 'dnc') class="active" @endif><a href="{{action('DncController@index')}}">{{__('tools.dnc_importer')}}</a></li>

    <li @if ($toolpage == 'email_drip') class="active" @endif><a href="{{action('EmailDripController@index')}}">{{__('tools.email_drip_builder')}}</a></li>
</ul>
