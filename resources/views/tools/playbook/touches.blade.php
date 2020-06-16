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
                            <a data-playbookid="{{$contacts_playbook->id}}" data-toggle="modal" data-target="#editPlaybookModal" href="#" class="flt_rgt edit_playbook_modal"><i class="fas fa-edit"></i> Edit Playbook</a>
                            <h3 class="playbook_name">{{$contacts_playbook->name}}</h3>
                            <h3 class="playbook_campaign">{{$contacts_playbook->campaign}} {{$contacts_playbook->subcampaign ? ': ' . $contacts_playbook->subcampaign : ''}}</h3>
                            <a href="{{action('PlaybookTouchController@addPlaybookTouchForm', [$contacts_playbook->id])}}" class="btn btn-primary flt_lft mb0 mt20">Add Touch</a>
                            <a href="{{action('PlaybookController@index')}}" class="btn btn-secondary flt_rgt mb0 mt20">Go Back</a>
                        </div>
			    	</div>
				</div>

                <div class="row touches">
                    @foreach($playbook_touches as $touch)
                        <div class="touch col-sm-2">
                            <a href="{{action('SmsFromNumberController@index')}}"><i class="fas fa-fingerprint fa-3x"></i></a>
                            <h4 class="name">{{$touch->name}}</h4>
                            <label class="switch">
                                <input type="checkbox" {{ ($touch->active) ? 'checked' : '' }} data-id="{{$touch->id}}" name="touch_input">
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

<!-- Edit Playbook Modal -->
<div class="modal fade" id="editPlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_playbook')}}</h4>
            </div>

            <form action="#" method="post" class="form edit_playbook">
                <div class="modal-body">
                    @include('tools.playbook.shared.playbook_form')
                    <input type="hidden" name="id" class="id" value="">
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-danger flt_lft delete_playbook_modal" data-toggle="modal" data-target="#deletePlaybookModal">{{__('tools.delete_playbook')}}</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button><button type="submit" class="btn btn-primary edit_playbook add_btn_loader">{{__('tools.save_changes')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE Playbook Modal -->
<div class="modal fade" id="deletePlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_playbook')}}</h4>
            </div>

            <form action="#" method="post" class="form edit_playbook">
                <div class="modal-body">
                    <input type="hidden" name="id" class="id" value="">
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-danger flt_lft delete_playbook">{{__('tools.delete_playbook')}}</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button><button type="submit" class="btn btn-primary delete_playbook add_btn_loader">{{__('tools.delete')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection