<ul class="nav nav-tabs">
    <li @if ($playbook_page == 'campaigns') class="active" @endif><a href="{{action('PlaybookController@index')}}">{{__('tools.playbook_campaigns')}}</a></li>
    <li @if ($playbook_page == 'filters') class="active" @endif><a href="{{action('PlaybookController@FilterIndex')}}">{{__('tools.playbook_filters')}}</a></li>
    <li @if ($playbook_page == 'actions') class="active" @endif><a href="{{action('PlaybookController@ActionIndex')}}">{{__('tools.playbook_actions')}}</a></li>
    <li @if ($playbook_page == 'providers') class="active" @endif><a href="{{action('PlaybookController@EmailServiceProviderIndex')}}">{{__('tools.email_service_providers')}}</a></li>
</ul>