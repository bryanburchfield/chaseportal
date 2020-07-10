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
                            <li class="breadcrumb-item"><a href="{{action('PlaybookHistoryController@index')}}">{{__('tools.history')}}</a></li>
                                <li class="breadcrumb-item">{{__('tools.playbook')}}</li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $playbook_run->contacts_playbook->name }} : {{ $playbook_run->created_at }}</li>
                            </ol>
                        </nav>

                        <div class="table-responsive nobdr playbooks_history_table">
                            <table class="table mt20 table-striped">
                                <thead>
                                    <tr>
                                        <th>{{__('tools.touch')}}</th>
                                        <th>{{__('tools.action')}}</th>
                                        <th>{{__('tools.processed')}}</th>
                                        <th>{{__('tools.reversed')}}</th>
                                        <th>{{__('tools.details')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($history as $item)
                                    <tr>
                                        <td>{{ $item['touch_name'] }}</td>
                                        <td>{{ $item['action_name'] }}</td>
                                        <td>
                                            @if (empty($item['processed_at']))
                                                {{__('tools.in_process')}}
                                            @else
                                                {{$item['processed_at']}}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item['action_type'] !== 'lead')
                                                {{$item['reversed_at']}}
                                            @else
                                                @if (!empty($item['processed_at']) && empty($item['reverse_started_at']))
                                                    <a class="btn btn-danger reverse_action" data-toggle="modal" data-target="#reverseActionModal" href="#" data-id="{{$item['id']}}"><i class="fas fa-history"></i> {{__('tools.reverse')}}</a>
                                                @elseif (!empty($item['processed_at']) && empty($item['reversed_at']))
                                                    {{__('tools.in_process')}}
                                                @else
                                                    {{$item['reversed_at']}}
                                                @endif
                                            @endif
                                        </td>
                                        <td><a href="{{ action('PlaybookHistoryController@runActionIndex', [$item['id']]) }}">Details</a></td>
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