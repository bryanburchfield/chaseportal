@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')
<div class="preloader"></div>
<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">
			    	<div class="col-sm-12">
                        <h2 class="bbnone">{{__('tools.contacts_playbook')}}</h2>
                        @include('tools.playbook.shared.topnav', ['playbook_page' => 'history'])
			    	</div>
                </div>

                <div class="row mt30">                    
                    <div class="col-sm-12">
                        <nav aria-label="breadcrumb" class="mb20">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ action('PlaybookHistoryController@index') }}">{{__('tools.history')}}</a></li>
                                <li class="breadcrumb-item"><a href="{{ action('PlaybookHistoryController@runIndex', [$playbook_run->id]) }}">{{__('tools.playbook')}}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{__('tools.action')}}
                                    {{ $playbook_run->contacts_playbook->name }} :
                                    {{ $playbook_run->created_at }} :
                                    {{ $playbook_run_touch_action->playbook_run_touch->playbook_touch->name }} : 
                                    {{ $playbook_run_touch_action->playbook_action->name }} </li>
                            </ol>
                        </nav>

                        <div class="table-responsive nobdr playbooks_history_table" >
                            <table class="table mt20 table-striped" id="run_action_playbooks_history_table">
                                <thead>
                                    <tr>
                                        <th>{{__('tools.lead')}} #</th>
                                        <th>{{__('tools.first_name')}}</th>
                                        <th>{{__('tools.last_name')}}</th>
                                        @switch($playbook_run_touch_action->playbook_action->action_type)
                                            @case('lead')
                                                <th>{{__('tools.old_campaign')}}</th>
                                                <th>{{__('tools.old_subcampaign')}}</th>
                                                <th>{{__('tools.old_callstatus')}}</th>
                                                <th>{{__('tools.current_campaign')}}</th>
                                                <th>{{__('tools.current_subcampaign')}}</th>
                                                <th>{{__('tools.current_callstatus')}}</th>
                                                @break
                                            @case('email')
                                                <th>{{__('tools.email')}}</th>
                                                @break
                                            @case('sms')
                                                <th>{{__('tools.phone')}}</th>
                                                @break
                                        @endswitch
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($details as $detail)
                                        <tr>
                                            <td>{{ $detail['lead_id'] }}</td>
                                            <td>{{ $detail['FirstName'] }}</td>
                                            <td>{{ $detail['LastName'] }}</td>
                                            @switch($playbook_run_touch_action->playbook_action->action_type)
                                                @case('lead')
                                                    <td>{{ $detail['old_campaign'] }}</td>
                                                    <td>{{ $detail['old_subcampaign'] }}</td>
                                                    <td>{{ $detail['old_callstatus'] }}</td>
                                                    <td>{{ $detail['Campaign'] }}</td>
                                                    <td>{{ $detail['Subcampaign'] }}</td>
                                                    <td>{{ $detail['CallStatus'] }}</td>
                                                    @break
                                                @case('email')
                                                    <td>{{ $detail['old_email'] }}</td>
                                                    @break
                                                @case('sms')
                                                    <td>{{ $detail['old_phone'] }}</td>
                                                    @break
                                            @endswitch
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
			</div>
		</div>
	</div>

    @include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@endsection