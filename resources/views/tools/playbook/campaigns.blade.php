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
                                @include('tools.playbook.shared.topnav', ['playbook_page' => 'campaigns'])

								<div class="tab-pane mt30" id="playbook_campaigns">
                                    <div class="col-sm-12 nopad">
                                        <a href="#" data-toggle="modal" data-target="#addCampaignModal" class="btn btn-primary add_playbook_campaign_modal">{{__('tools.add_campaign')}}</a>
                                        
                                        <div class="table-responsive nobdr campaigns">
                                            <table class="table mt20">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('tools.name')}}</th>
                                                        <th>{{__('tools.campaign')}}</th>
                                                        <th>{{__('tools.subcampaign')}}</th>
                                                        <th>{{__('tools.active')}}</th>
                                                        <th>{{__('tools.edit')}}</th>
                                                        <th>{{__('tools.delete')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(count($playbook_campaigns))
                                                        @foreach($playbook_campaigns as $playbook_campaign)
                                                            <tr>
                                                                <td>{{$playbook_campaign->name}}</td>
                                                                <td>{{$playbook_campaign->campaign}}</td>
                                                                <td>{{$playbook_campaign->subcampaign}}</td>
                                                                <td>{{$playbook_campaign->active}}</td>
                                                                <?php $mode='edit';?>
                                                                <td><a href="#" data-toggle="modal" data-target="#editCampaignModal" class=" edit_playbook_campaign_modal" data-playbook_campaignid="{{$playbook_campaign->id}}"><i class="fas fa-edit"></i></a></td>
                                                                <td><a class="remove_playbook_campaign_modal" data-toggle="modal" data-target="#deleteCampaignModal" href="#" data-name="{{$playbook_campaign->name}}" data-id="{{$playbook_campaign->id}}"><i class="fa fa-trash-alt"></i></a></td>
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

<!-- Add Campaign Modal -->
<div class="modal fade" id="addCampaignModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_campaign')}}</h4>
            </div>
            
            <form action="#" method="post" class="form add_campaign">
                <div class="modal-body">
                    @include('tools.playbook.shared.campaign_form')
                    <input type="hidden" name="playbook_campaign_id" id="playbook_campaign_id" value="">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                    <input type="submit" class="btn btn-primary add_campaign" value="{{__('tools.add_campaign')}}">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Campaign Modal -->
<div class="modal fade" id="editCampaignModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_campaign')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form edit_campaign">
                    @include('tools.playbook.shared.campaign_form')
                    <input type="hidden" name="id" class="id" value="">
                    <button type="submit" class="btn btn-primary edit_campaign add_btn_loader">{{__('tools.save_changes')}}</button>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Campaign Modal -->
<div class="modal fade" id="deleteCampaignModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_campaign')}}</h4>
            </div>

            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}} <span></span>?</h3>
                <input type="hidden" name="id" id="id" value="">
                <div class="alert alert-danger hidetilloaded mt20"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                <button type="button" class="btn btn-danger delete_playbook_campaign add_btn_loader"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</button>
            </div>
        </div>
    </div>
</div>

@endsection