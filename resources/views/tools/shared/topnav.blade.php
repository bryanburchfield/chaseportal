<ul class="nav nav-tabs">
    <li @if ($toolpage == 'playbook') class="active" @endif><a href="{{action('PlaybookController@index')}}">{{__('tools.contacts_playbook')}}</a></li>
    <li @if ($toolpage == 'dnc') class="active" @endif><a href="{{url('/tools/dnc_importer')}}">{{__('tools.dnc_importer')}}</a></li>
</ul>
