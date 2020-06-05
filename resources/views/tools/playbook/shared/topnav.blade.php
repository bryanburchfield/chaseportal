<ul class="nav nav-tabs">
    <li @if ($playbook_page == 'playbooks') class="active" @endif><a href="{{action('PlaybookController@index')}}">{{__('tools.playbooks')}}</a></li>
    <li @if ($playbook_page == 'filters') class="active" @endif><a href="{{action('PlaybookFilterController@index')}}">{{__('tools.playbook_filters')}}</a></li>
    <li @if ($playbook_page == 'actions') class="active" @endif><a href="{{action('PlaybookActionController@index')}}">{{__('tools.playbook_actions')}}</a></li>
    <li @if ($playbook_page == 'providers') class="active" @endif><a href="{{action('PlaybookEmailProviderController@index')}}">{{__('tools.email_service_providers')}}</a></li>
    @if (Auth::user()->isType('superadmin'))
        <li @if ($playbook_page == 'sms_numbers') class="active" @endif><a href="{{action('SmsFromNumberController@index')}}">SMS Numbers</a></li>
    @endif
</ul>