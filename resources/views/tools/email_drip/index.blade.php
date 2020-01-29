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
			    		@include('tools.shared.topnav', ['toolpage' => 'email_drip'])

			    		<div class="tab-content">
                            <div class="tab-pane active mt30">
                                <h2 class="bbnone">{{__('tools.email_drip_builder')}}</h2>
                                <ul class="nav nav-tabs tabs tools_subnav">
                                    <li class="active"><a href="#drip_campaigns" data-toggle="tab">{{__('tools.email_drip_campaigns')}}</a></li>
                                    <li><a href="#email_service_providers" data-toggle="tab">{{__('tools.email_service_providers')}}</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane active mt30" id="drip_campaigns">
                                        <div class="col-sm-12 nopad">
											<a href="#" class="btn btn-primary create_new_drip" data-toggle="modal" data-target="#createCampaignModal">{{__('tools.create_campaign')}}</a>
                                        	<div class="table-responsive nobdr drip_campaigns">
                                        		<table class="table mt20">
                                        			<thead>
                                        				<tr>
                                                            <th>{{__('tools.active')}}</th>
                                        					<th>{{__('tools.name')}}</th>
                                                            <th>{{__('tools.description')}}</th>
                                        					<th>{{__('tools.campaign')}}</th>
                                        					<th>{{__('tools.subcampaign')}}</th>
                                        					<th>{{__('tools.server_id')}}</th>
                                                            <th>{{__('tools.emails_per_lead')}}</th>
                                                            <th>{{__('tools.days_between_emails')}}</th>
                                                            <th>{{__('tools.edit')}}</th>
                                                            <th>{{__('tools.delete')}}</th>
                                        				</tr>
                                        			</thead>

                                        			<tbody>
                                                        @foreach($email_drip_campaigns as $drip)
                                            				<tr>
                                            					<td>
                                                                    <label class="switch email_campaign_switch">
                                                                        <input type="checkbox" {{ ($drip->active) ? 'checked' : '' }} name="email_input" data-id="{{$drip->id}}">
                                                                        <span></span>
                                                                    </label>
                                                                </td>
                                            					<td>{{$drip->name}}</td>
                                            					<td>{{$drip->description}}</td>
                                            					<td>{{$drip->campaign}}</td>
                                                                <td>{{$drip->subcampaign}}</td>
                                                                <td>{{$drip->email_service_provider_id}}</td>
                                                                <td>{{$drip->emails_per_lead}}</td>
                                                                <td>{{$drip->days_between_emails}}</td>
                                                                <td><a href="#" data-toggle="modal" data-target="#editCampaignModal" class=" edit_campaign_modal" data-campaignid="{{$drip->id}}"><i class="fas fa-edit"></i></a></td>
                                                                <td><a class="remove_campaign_modal" data-toggle="modal" data-target="#deleteCampaignModal" href="#" data-name="{{$drip->name}}" data-id="{{$drip->id}}"><i class="fa fa-trash-alt"></i></a></td>
                                            				</tr>
                                                        @endforeach
                                        			</tbody>
                                        		</table>
                                        	</div>
                                        </div>
                                    </div>

                                    <div class="tab-pane mt30" id="email_service_providers">
                                        <div class="col-sm-12 nopad">
                                            <a href="#" data-toggle="modal" data-target="#addProviderModal" class="btn btn-primary add_server_modal">{{__('tools.add_provider')}}</a>

                                            <div class="table-responsive nobdr email_service_providers">
                                                <table class="table mt20">
                                                    <thead>
                                                        <tr>
                                                            <th>{{__('tools.name')}}</th>
                                                            <th>{{__('tools.provider_type')}}</th>
                                                            <th>{{__('tools.properties')}}</th>
                                                            <th>{{__('tools.edit')}}</th>
                                                            <th>{{__('tools.delete')}}</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        
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
	</div>
</div>

@include('shared.reportmodal')

<!-- Add SMTP Server Modal -->
<div class="modal fade" id="addProviderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_provider')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form add_email_service_provider">
                    @include('tools.email_drip.email_service_provider')
                    <input type="submit" class="btn btn-primary add_email_service_provider" value="{{__('tools.add_provider')}}">
                    <button type="submit" class="btn btn-info test_connection btn_flt_rgt add_btn_loader">{{__('tools.test_connection')}}</button>
                </form>
                <input type="hidden" name="email_service_provider_id" id="email_service_provider_id" value="">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit SMTP Server Modal -->
<div class="modal fade" id="editServerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_server')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form edit_email_service_provider">
                    @include('tools.email_drip.email_service_provider')
                    <input type="hidden" name="id" class="id" value="">
                    <button type="submit" class="btn btn-primary edit_email_service_provider add_btn_loader">{{__('tools.save_changes')}}</button>
                    <button type="submit" class="btn btn-info test_connection btn_flt_rgt add_btn_loader">{{__('tools.test_connection')}}</button>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.create_drip_campaign')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form create_campaign_form">
                    @include('tools.email_drip.shared.campaign_form_fields')

                    <div class="alert alert-success hidetilloaded"></div>
                    <div class="alert alert-danger hidetilloaded"></div>
                    <button type="submit" class="btn btn-primary create_campaign add_btn_loader mt10">{{__('tools.create_campaign')}}</button>
                </form>
            </div>

	        <div class="modal-footer">
	            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
	        </div>
	    </div>
    </div>
</div>

<!-- Edit Campaign Modal -->
<div class="modal fade" id="editCampaignModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_drip_campaign')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form edit_campaign_form">

                    @include('tools.email_drip.shared.campaign_form_fields')

                    <div class="alert alert-success hidetilloaded"></div>
                    <div class="alert alert-danger hidetilloaded"></div>
                    <button type="submit" class="btn btn-primary edit_campaign add_btn_loader mt10">{{__('tools.save_changes')}}</button>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete SMTP Server Modal -->
<div class="modal fade" id="deleteEmailServiceProviderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_server')}}</h4>
            </div>

            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}} <span></span></h3>
                <input type="hidden" name="id" id="id" value="">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
                <button type="button" class="btn btn-danger delete_email_service_provider add_btn_loader">{{__('tools.delete')}}</button>
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
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
                <button type="button" class="btn btn-danger delete_campaign add_btn_loader">{{__('tools.delete')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Campaign Filter Modal -->
<div class="modal fade" id="campaignFilterModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.campaign_filters')}}</h4>
            </div>

            <div class="modal-body">
    
                <input type="hidden" name="id" id="id" value="">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
                <button type="button" class="btn btn-danger save_filters add_btn_loader">{{__('tools.save_changes')}}</button>
            </div>
        </div>
    </div>
</div>


@endsection