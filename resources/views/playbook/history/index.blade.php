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
                        @include('playbook.shared.topnav', ['playbook_page' => 'history'])
			    	</div>
                </div>

                <div class="table-responsive nobdr playbooks_history_table mt30">
                    <table class="table mt20 table-striped" id="playbooks_history_table">
                        <thead>
                            <tr>
                                <th>{{__('tools.date')}}</th>
                                <th>{{__('tools.playbook')}}</th>
                                <th>{{__('tools.records')}}</th>
                                <th>{{__('tools.details')}}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($history as $item)
                            <tr>
                                <td>{{$item->created_at}}</td>
                                <td>{{$item->contacts_playbook->name}}</td>
                                <td>{{$item->record_count()}}</td>
                                <td><a href="{{ action('PlaybookHistoryController@runIndex', [$item->id]) }}"><i class="fas fa-external-link-alt"></i> {{__('tools.details')}}</a></td>
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