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
                                    <li><a href="#smtp_servers" data-toggle="tab">{{__('tools.smtp_servers')}}</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane active mt30" id="drip_campaigns">
                                        <div class="col-sm-12 nopad">
											<a href="#" class="btn btn-primary create_new_drip" data-toggle="modal" data-target="#createCampaignModal">{{__('tools.create_campaign')}}</a>
                                        	<div class="table-responsive nobdr drip_campaigns">
                                        		<table class="table mt20">
                                        			<thead>
                                        				<tr>
                                                            <th>Active</th>
                                        					<th>Name</th>
                                                            <th>Description</th>
                                        					<th>Campaign</th>
                                        					<th>Sub Campaign</th>
                                        					<th>Server ID</th>
                                                            <th>Edit</th>
                                                            <th>Delete</th>
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
                                                                <td>{{$drip->smtp_server_id}}</td>
                                                                <td><a href="#" data-toggle="modal" data-target="#editCampaignModal" class=" edit_campaign_modal" data-campaignid="{{$drip->id}}"><i class="fas fa-edit"></i></a></td>
                                                                <td><a class="remove_campaign_modal" data-toggle="modal" data-target="#deleteCampaignModal" href="#" data-name="{{$drip->name}}" data-id="{{$drip->id}}"><i class="fa fa-trash-alt"></i></a></td>
                                            				</tr>
                                                        @endforeach
                                        			</tbody>
                                        		</table>
                                        	</div>
                                        </div>
                                    </div>

                                    <div class="tab-pane mt30" id="smtp_servers">
                                        <div class="col-sm-12 nopad">
                                            <a href="#" data-toggle="modal" data-target="#addServerModal" class="btn btn-primary add_server_modal">{{__('tools.add_server')}}</a>

                                            <div class="table-responsive nobdr smtp_servers">
                                                <table class="table mt20">
                                                    <thead>
                                                        <tr>
                                                            <th>{{__('tools.name')}}</th>
                                                            <th>{{__('tools.host')}}</th>
                                                            <th>{{__('tools.port')}}</th>
                                                            <th>{{__('tools.username')}}</th>
                                                            <th>{{__('tools.edit')}}</th>
                                                            <th>{{__('tools.delete')}}</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @if(count($smtp_servers))
                                                            @foreach($smtp_servers as $server)
                                                                <tr>
                                                                    <td>{{$server->name}}</td>
                                                                    <td>{{$server->host}}</td>
                                                                    <td>{{$server->port}}</td>
                                                                    <td>{{$server->username}}</td>
                                                                    <?php $mode='edit';?>
                                                                    <td><a href="#" data-toggle="modal" data-target="#editServerModal" class=" edit_server_modal" data-serverid="{{$server->id}}"><i class="fas fa-edit"></i></a></td>
                                                                    <td><a class="remove_smtp_server_modal" data-toggle="modal" data-target="#deleteSmtpServerModal" href="#" data-name="{{$server->name}}" data-id="{{$server->id}}"><i class="fa fa-trash-alt"></i></a></td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <div class="alert alert-info">{{__('tools.no_servers_added')}}</div>
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
	</div>
</div>

@include('shared.reportmodal')

<!-- Add SMTP Server Modal -->
<div class="modal fade" id="addServerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_server')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form add_smtp_server">
                    @include('tools.email_drip.smtp_server')
                    <input type="submit" class="btn btn-primary add_smtp_server" value="{{__('tools.add_server')}}">
                    <button type="submit" class="btn btn-info test_connection btn_flt_rgt add_btn_loader">{{__('tools.test_connection')}}</button>
                </form>
                <input type="hidden" name="smtp_server_id" id="smtp_server_id" value="">
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
                <form action="#" method="post" class="form edit_smtp_server">
                    @include('tools.email_drip.smtp_server')
                    <input type="hidden" name="id" class="id" value="">
                    <button type="submit" class="btn btn-primary edit_smtp_server add_btn_loader">{{__('tools.save_changes')}}</button>
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
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" class="form-control description" name="description" required>
                    </div>

                    <div class="form-group">
                        <label>Campaign</label>
                        <select name="campaign" class="form-control campaign drip_campaigns_campaign_menu"  required>
                            <option value="">Select One</option>
                            @foreach($campaigns as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>SubCampaign</label>
                        <select name="subcampaign" class="form-control drip_campaigns_subcampaign" required>
                            <option value="">Select One</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <select name="email_field" class="form-control email" required>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Templates</label>
                        <select name="template_id" class="template_id form-control">
                            <option value="">Select One</option>
                            @foreach($templates as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Server Name</label>
                        <select name="smtp_server_id" class="form-control smtp_server_id" required>
                            <option value="">Select One</option>
                            @foreach($smtp_servers as $server)
                                <option value="{{$server->id}}">{{$server->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-success hidetilloaded"></div>
                    <div class="alert alert-danger hidetilloaded"></div>
                    <button type="submit" class="btn btn-primary create_campaign add_btn_loader mt10">Create Campaign</button>
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
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" class="form-control description" name="description" required>
                    </div>

                    <div class="form-group">
                        <label>Campaign</label>
                        <select name="campaign" class="form-control campaign drip_campaigns_campaign_menu"  required>
                            <option value="">Select One</option>
                            @foreach($campaigns as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>SubCampaign</label>
                        <select name="subcampaign"class="form-control drip_campaigns_subcampaign" required>
                            <option value="">Select One</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <select name="email_field" class="form-control email" required>
                            <option value="">Select One</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Templates</label>
                        <select name="template_id" class="template_id form-control">
                            <option value="">Select One</option>
                            @foreach($templates as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Server Name</label>
                        <select name="smtp_server_id" class="form-control smtp_server_id" required>
                            <option value="">Select One</option>
                            @foreach($smtp_servers as $server)
                                <option value="{{$server->id}}">{{$server->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="id" class="id">

                    <div class="alert alert-success hidetilloaded"></div>
                    <div class="alert alert-danger hidetilloaded"></div>
                    <button type="submit" class="btn btn-primary edit_campaign add_btn_loader mt10">Save Changes</button>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete SMTP Server Modal -->
<div class="modal fade" id="deleteSmtpServerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
                <button type="button" class="btn btn-danger delete_smtp_server add_btn_loader">{{__('tools.delete')}}</button>
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
                <h4 class="modal-title" id="myModalLabel">Delete Campaign</h4>
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


@endsection