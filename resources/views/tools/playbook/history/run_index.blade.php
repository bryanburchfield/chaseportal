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
                    <a href="{{action('PlaybookHistoryController@index')}}">History</a>
                    ->Playbook
                    <hr>
                    {{ $playbook_run->contacts_playbook->name }} : {{ $playbook_run->created_at }}
                    <hr>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Touch</th>
                                <th>Action</th>
                                <th>Processed</th>
                                <th>Reversed</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $item)
                            <tr>
                                <td>{{ $item['touch_name'] }}</td>
                                <td>{{ $item['action_name'] }}</td>
                                <td>{{ $item['processed_at'] }}</td>
                                <td>{{ $item['reversed_at'] }}</td>
                                <td><a href="{{ action('PlaybookHistoryController@runActionIndex', [$item['id']]) }}">Details</a></td>
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