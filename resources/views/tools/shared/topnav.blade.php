<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link @if ($toolpage == 'playbook') active @endif" href="{{action('PlaybookController@index')}}">{{__('tools.contacts_playbook')}}</a></li>
    <li class="nav-item"><a class="nav-link @if ($toolpage == 'dnc') active @endif" href="{{url('/tools/dnc_importer')}}">{{__('tools.dnc_importer')}}</a></li>
</ul>
