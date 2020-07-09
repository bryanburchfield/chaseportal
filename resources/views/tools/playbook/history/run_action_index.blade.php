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
                <div>
                    <a href="{{ action('PlaybookHistoryController@index') }}">History</a>
                    -><a href="{{ action('PlaybookHistoryController@runIndex', [$playbook_run->id]) }}">Playbook</a>
                    ->Action
                    <hr>
                    {{ $playbook_run->contacts_playbook->name }} :
                    {{ $playbook_run->created_at }} :
                    {{ $playbook_run_touch_action->playbook_run_touch->playbook_touch->name }} : 
                    {{ $playbook_run_touch_action->playbook_action->name }} : 
                    <hr>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Lead#</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($details as $detail)
                                <tr>
                                    <td>{{ $detail['id'] }}</td>
                                    <td>{{ $detail['FirstName'] }}</td>
                                    <td>{{ $detail['LastName'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
			</div>
		</div>
	</div>

    @include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@endsection