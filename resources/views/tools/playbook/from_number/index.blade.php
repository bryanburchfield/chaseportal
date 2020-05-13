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
                        @include('tools.shared.topnav', ['toolpage' => 'playbook'])
                        
			    		<div class="tab-content">
                            <div class="tab-pane active mt30">
                                <h2 class="bbnone">{{__('tools.contacts_playbook')}}</h2>
                                @include('tools.playbook.shared.topnav', ['playbook_page' => 'sms_numbers'])

								<div class="tab-pane mt30" id="sms_numbers">
                                    <div class="col-sm-12 nopad">
                                        <div class="table-responsive nobdr sms_numbers mt20">
                                            <div>
                                                <a style="margin: 19px;" href="{{ route('sms_numbers.create')}}" class="btn btn-primary">Add Number</a>
                                            </div>
                                            <table class="table mt20 table-striped" id="sms_numbers_datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Group</th>
                                                        <th>Number</th>
                                                        <th colspan=2>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(count($playbook_sms_numbers))
                                                        @foreach($playbook_sms_numbers as $playbook_sms_number)
                                                            <tr data-playbook_sms_number_id="{{$playbook_sms_number->id}}">
                                                                <td>{{$playbook_sms_number->group_id}}</td>
                                                                <td>{{$playbook_sms_number->from_number}}</td>
                                                                <td>
                                                                    <a href="{{ route('sms_numbers.edit',$playbook_sms_number->id)}}" class="btn btn-primary">Edit</a>
                                                                </td>
                                                                <td>
                                                                    <form action="{{ route('sms_numbers.destroy', $playbook_sms_number->id)}}" method="post">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="btn btn-danger" type="submit">Delete</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
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