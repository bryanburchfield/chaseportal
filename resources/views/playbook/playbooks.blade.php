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

			    		<div class="tab-content">
                            <div class="tab-pane active">
                                <h2 class="bbnone">{{__('tools.contacts_playbook')}}</h2>
                                @include('playbook.shared.topnav', ['playbook_page' => 'playbooks'])

								<div class="tab-pane mt30" id="contact_playbooks">
                                    <div class="col-sm-12 nopad">
                                        <a href="#" data-toggle="modal" data-target="#addPlaybookModal" class="btn btn-primary add_playbook_modal"><i class="fas fa-plus-circle"></i> {{__('tools.add_playbook')}}</a>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12 mt30">
                                            @foreach($contacts_playbooks as $contacts_playbook)
                                                <div class="playbook col-sm-2 mb30" data-playbook="{{$contacts_playbook->id}}">
                                                    <a href="#"
                                                        class="menu"
                                                        data-toggle="popover"
                                                        data-trigger="focus"
                                                        data-content="<div>
                                                            <ul>
                                                                <li><a href='{{ action("PlaybookTouchController@index", ['contacts_playbook' => $contacts_playbook])}}'>{{__('tools.touches')}}</a></li>
                                                                <li><a href='#' class='edit_playbook_modal' data-id='{{$contacts_playbook->id}}' data-toggle='modal' data-target='#editPlaybookModal'>{{__('tools.edit')}}</a></li>
                                                                <li><a href='#' class='delete_playbook_modal' data-id='{{$contacts_playbook->id}}' data-toggle='modal' data-name='{{$contacts_playbook->name}}' data-target='#deletePlaybookModal'>{{__('tools.delete')}}</a></li>
                                                            </ul></div>">
                                                        <i class="fas fa-book fa-3x"></i>
                                                    </a>

                                                    <h4 class="name">{{$contacts_playbook->name}}</h4>

                                                    <label class="switch">
                                                        <input type="checkbox" {{ ($contacts_playbook->active) ? 'checked' : '' }} name="playbook_input" class="toggle_playbook {{ ($contacts_playbook->active) ? 'checked' : '' }}">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 mt30">
                                            <div class="alert alert-danger hidetilloaded cb"></div>
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
@include('playbook.shared.playbook_modals')

<!-- Playbook Actions Modal -->
<div class="modal fade" id="actionPlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.manage_actions')}}</h4>
            </div>

            <div class="modal-body">
                <input type="hidden" name="id" id="id" value="">
                <div class="playbook_action_manager"></div>
                <div class="alert alert-danger hidetilloaded mt20"></div>
            </div>

            <div class="modal-footer">
                <img src="/img/loader_hor.gif" alt="" class="img-responsive loader_hor hidetilloaded mt10">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                <button type="button" class="btn btn-primary update_actions"> {{__('tools.save_changes')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Playbook Filters Modal -->
<div class="modal fade" id="filterPlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.manage_filters')}}</h4>
            </div>

            <div class="modal-body">
                <input type="hidden" name="id" id="id" value="">
                <div class="playbook_filter_manager"></div>
                <div class="alert alert-danger hidetilloaded mt20"></div>
            </div>

            <div class="modal-footer">
                <img src="/img/loader_hor.gif" alt="" class="img-responsive loader_hor hidetilloaded mt10">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                <button type="button" class="btn btn-primary update_filters"> {{__('tools.save_changes')}}</button>
            </div>
        </div>
    </div>
</div>

@endsection