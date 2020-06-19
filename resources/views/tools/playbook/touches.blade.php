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
                        <h2 class="mb20">{{__('tools.playbook_touches')}}</h2>

                        <div class="col-sm-6 mt0 p0 mb30 card">
                            <h3 class="playbook_name">{{$contacts_playbook->name}}</h3>
                            <h3 class="playbook_campaign">{{$contacts_playbook->campaign}} {{$contacts_playbook->subcampaign ? ': ' . $contacts_playbook->subcampaign : ''}}</h3>
                            <a href="{{action('PlaybookTouchController@addPlaybookTouchForm', [$contacts_playbook->id])}}" class="btn btn-primary flt_lft mb0 mt20">{{__('tools.add_touch')}}</a>
                            <a href="{{action('PlaybookController@index')}}" class="btn btn-secondary flt_rgt mb0 mt20">{{__('widgets.go_back')}}</a>
                        </div>
			    	</div>
				</div>

                <div class="row touches mt30">
                    @foreach($playbook_touches as $touch)
                        <div class="touch col-sm-2">
                            <a href="#"
                                class="menu"
                                data-toggle="popover"
                                data-trigger="focus"
                                data-content="<div>
                                    <ul>
                                        <li><a href='{{ action('PlaybookTouchController@updatePlaybookTouchForm', ['id' => $touch->id])}}'>{{__('tools.edit')}}</a></li>
                                        <li><a href='#' class='delete_touch_modal' data-id='{{$touch->id}}' data-toggle='modal' data-target='#deleteTouchModal' data-name='{{$touch->name}}'>{{__('tools.delete')}}</a></li>
                                    </ul></div>">
                                <i class="fas fa-fingerprint"></i>
                            </a>

                            <h4 class="name">{{$touch->name}}</h4>
                            <label class="switch">
                                <input type="checkbox" {{ ($touch->active) ? 'checked' : '' }} data-id="{{$touch->id}}" name="touch_input" class="toggle_touch {{ ($touch->active) ? 'checked' : '' }}">
                                <span></span>
                            </label>
                        </div>
                    @endforeach

                    <div class="row">
                        <div class="col-sm-6 mt30">
                            <div class="alert alert-danger hidetilloaded cb"></div>
                        </div>
                    </div>
                </div>
			</div>
		</div>
	</div>

    @include('shared.notifications_bar')
</div>

@include('shared.reportmodal')
@include('tools.playbook.shared.playbook_modals')


@endsection