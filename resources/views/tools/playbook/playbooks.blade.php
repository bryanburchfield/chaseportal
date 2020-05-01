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
                                @include('tools.playbook.shared.topnav', ['playbook_page' => 'playbooks'])

								<div class="tab-pane mt30" id="contact_playbooks">
                                    <div class="col-sm-12 nopad">
                                        <a href="#" data-toggle="modal" data-target="#addPlaybookModal" class="btn btn-primary add_playbook_modal">{{__('tools.add_playbook')}}</a>
                                        
                                        <div class="table-responsive nobdr playbooks">
                                            <table class="table mt20">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('tools.name')}}</th>
                                                        <th>{{__('tools.campaign')}}</th>
                                                        <th>{{__('tools.subcampaign')}}</th>
                                                        <th>{{__('tools.filters')}}</th>
                                                        <th>{{__('tools.actions')}}</th>
                                                        <th>{{__('tools.edit')}}</th>
                                                        <th>{{__('tools.delete')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(count($contacts_playbooks))
                                                        @foreach($contacts_playbooks as $playbook)
                                                            <tr data-playbook_id="{{$playbook->id}}">
                                                                <td>{{$playbook->name}}</td>
                                                                <td>{{$playbook->campaign}}</td>
                                                                <td>
                                                                    @empty($playbook->subcampaign)
                                                                        <i>{{__('tools.any')}}</i><br>
                                                                    @endempty
                                                                    @isset($playbook->subcampaign)
                                                                        @if ($playbook->subcampaign == '!!none!!')
                                                                            <i>{{__('tools.no_subcampaign')}}</i><br>
                                                                        @else
                                                                            {{$playbook->subcampaign}}<br>
                                                                        @endif
                                                                    @endisset
                                                                </td>
                                                                <td>
                                                                    @if(count($playbook->filters))
                                                                        @foreach ($playbook->filters as $filter)
                                                                            <a href="#" data-toggle="modal" data-target="#filterPlaybookModal" class="playbook_filters_modal" data-campaign="{{$playbook->campaign}}" data-is_empty="0" data-playbookid="{{$playbook->id}}"><i class="fas fa-edit"></i> {{$filter->playbook_filter->name}}</a>
                                                                        @endforeach
                                                                    @else
                                                                        <a href="#" data-toggle="modal" data-target="#filterPlaybookModal" class="playbook_filters_modal" data-campaign="{{$playbook->campaign}}" data-is_empty="1" data-playbookid="{{$playbook->id}}"><i class="far fa-eye"></i></a>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if(count($playbook->actions))
                                                                        @foreach ($playbook->actions as $action)
                                                                            <a href="#" data-toggle="modal" data-campaign="{{$playbook->campaign}}" data-target="#actionPlaybookModal" data-playbookid="{{$playbook->id}}" data-is_empty="0" class="playbook_actions_modal" data-campaign="{{$playbook->campaign}}"><i class="fas fa-edit"></i> {{$action->playbook_action->name}}</a>
                                                                        @endforeach
                                                                    @else
                                                                        <a href="#" data-toggle="modal" data-target="#actionPlaybookModal" class="playbook_actions_modal" data-campaign="{{$playbook->campaign}}" data-is_empty="1" data-playbookid="{{$playbook->id}}"><i class="far fa-eye"></i></a>
                                                                    @endif
                                                                </td>
                                                                <?php $mode='edit';?>
                                                                <td><a href="#" data-toggle="modal" data-target="#editPlaybookModal" class=" edit_playbook_modal" data-playbookid="{{$playbook->id}}"><i class="fas fa-edit"></i></a></td>
                                                                <td><a class="remove_playbook_modal" data-toggle="modal" data-target="#deletePlaybookModal" href="#" data-name="{{$playbook->name}}" data-playbookid="{{$playbook->id}}"><i class="fa fa-trash-alt"></i></a></td>
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

<!-- Add Playbook Modal -->
<div class="modal fade" id="addPlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_playbook')}}</h4>
            </div>
            
            <form action="#" method="post" class="form add_playbook">
                <div class="modal-body">
                    @include('tools.playbook.shared.playbook_form')
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                    <input type="submit" class="btn btn-primary add_playbook" value="{{__('tools.add_playbook')}}">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Plpaybook Modal -->
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
                    <div class="alert alert-danger hidetilloaded"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button><button type="submit" class="btn btn-primary edit_playbook add_btn_loader">{{__('tools.save_changes')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Playbook Modal -->
<div class="modal fade" id="deletePlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_playbook')}}</h4>
            </div>

            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}} <span></span>?</h3>
                <input type="hidden" name="id" class="id" value="">
                <div class="alert alert-danger hidetilloaded mt20"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                <button type="button" class="btn btn-danger delete_playbook_playbook"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</button>
            </div>
        </div>
    </div>
</div>

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