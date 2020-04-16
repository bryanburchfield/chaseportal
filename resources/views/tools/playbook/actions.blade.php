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
                                @include('tools.playbook.shared.topnav', ['playbook_page' => 'actions'])

								<div class="tab-pane mt30" id="playbook_actions">
                                    <div class="col-sm-12 nopad">
                                        <a href="#" data-toggle="modal" data-target="#addActionModal" class="btn btn-primary add_playbook_action_modal">{{__('tools.add_action')}}</a>
                                        
                                        <div class="table-responsive nobdr actions">
                                            <table class="table table-striped mt20">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('tools.name')}}</th>
                                                        <th>{{__('tools.campaign')}}</th>
                                                        <th>{{__('tools.action_type')}}</th>
                                                        <th>{{__('tools.edit')}}</th>
                                                        <th>{{__('tools.delete')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(count($playbook_actions))
                                                        @foreach($playbook_actions as $playbook_action)
                                                            <tr>
                                                                <td>{{$playbook_action->name}}</td>
                                                                <td>{{$playbook_action->campaign}}</td>
                                                                <td>{{$playbook_action->action_type}}</td>
                                                                <?php $mode='edit';?>
                                                                <td><a href="#" data-toggle="modal" data-target="#editActionModal" class=" edit_playbook_action_modal" data-id="{{$playbook_action->id}}"><i class="fas fa-edit"></i></a></td>
                                                                <td><a class="remove_playbook_action_modal" data-toggle="modal" data-target="#deleteActionModal" href="#" data-name="{{$playbook_action->name}}" data-id="{{$playbook_action->id}}"><i class="fa fa-trash-alt"></i></a></td>
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

<!-- Add Action Modal -->
<div class="modal fade" id="addActionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_action')}}</h4>
            </div>

            <form action="#" method="post" class="form add_action">
                <div class="modal-body">
                    @include('tools.playbook.shared.action_form')
                    <input type="hidden" name="id" class="id" value="">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                    <input type="submit" class="btn btn-primary" value="{{__('tools.add_action')}}">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Action Modal -->
<div class="modal fade" id="editActionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_action')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form edit_action">
                    @include('tools.playbook.shared.action_form')
                    <input type="hidden" name="id" class="id" value="">
                    
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button><button type="submit" class="btn btn-primary edit_action add_btn_loader">{{__('tools.save_changes')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Action Modal -->
<div class="modal fade" id="deleteActionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_action')}}</h4>
            </div>

            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}} <span></span>?</h3>
                <input type="hidden" name="id" class="id" value="">
                <div class="alert alert-danger hidetilloaded mt20"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                <button type="button" class="btn btn-danger delete_playbook_action add_btn_loader"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</button>
            </div>
        </div>
    </div>
</div>

@endsection