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
                                <ul class="nav nav-tabs tabs tools_subnav">
                                    <li class="active"><a href="#drip_campaigns" data-toggle="tab">{{__('tools.email_drip_campaigns')}}</a></li>
                                    <li><a href="#providers" data-toggle="tab">{{__('tools.providers')}}</a></li>
                                    <li><a href="#templates" data-toggle="tab">{{__('tools.templates')}}</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane active mt30" id="drip_campaigns">
                                        <div class="col-sm-12 nopad">
											<a href="#" class="btn btn-primary create_new_drip" data-toggle="modal" data-target="#createCampaignModal">{{__('tools.create_campaign')}}</a>

                                        	<div class="table-responsive nobdr drip_campaigns">
                                        		<table class="table mt20">
                                        			<thead>
                                        				<tr>
                                        					<th>asdf</th>
                                        					<th>asdf</th>
                                        					<th>asdfasdf</th>
                                        					<th>asdf</th>
                                        					<th>sadfsadfds</th>
                                        				</tr>
                                        			</thead>

                                        			<tbody>
                                        				<tr>
                                        					<td></td>
                                        					<td></td>
                                        					<td></td>
                                        					<td></td>
                                        					<td></td>
                                        				</tr>
                                        			</tbody>
                                        		</table>
                                        	</div>
                                        </div>
                                    </div>

                                    <div class="tab-pane mt30" id="providers">
                                        <div class="col-sm-12 nopad">
                                        	<a href="#" class="btn btn-primary add_provider" data-toggle="modal" data-target="#addProviderModal">{{__('tools.add_provider')}}</a>

                                        	<div class="table-responsive nobdr drip_campaigns">
                                        		<table class="table mt20">
                                        			<thead>
                                        				<tr>
                                        					<th>Name</th>
                                        					<th>Provider</th>
                                        					<th>Username</th>
                                        					<th>Delete</th>
                                        				</tr>
                                        			</thead>

                                        			<tbody>

															@if(count($email_service_providers))
																@foreach($email_service_providers as $provider)
																<tr>
																	<td>{{$provider->name}}</td>
																	<td>{{$provider->provider}}</td>
																	<td>{{$provider->username}}</td>
																	<td><a class="provider_modal_link remove_user" data-toggle="modal" data-target="#deleteProviderModal" href="#" data-name="{{$provider->name}}" data-user="{{$provider->id}}"><i class="fa fa-trash-alt"></i></a></td>
																</tr>
																@endforeach
															@else
																<div class="alert alert-info">{{__('tools.no_providers')}}</div>
															@endif
                                        			</tbody>
                                        		</table>
                                        	</div>
                                        </div>
                                    </div>

                                    <div class="tab-pane mt30" id="templates">
                                        <div class="col-sm-12 nopad">
											<a href="#" class="btn btn-primary" data-toggle="modal" data-target="#uploadTemplateModal"><i class="fas fa-file-upload"></i> {{__('tools.upload_template')}}</a>

                                        	<div class="table-responsive nobdr drip_templates">
                                        		<table class="table mt20">
                                        			<thead>
                                        				<tr>
                                        					<th>asdf</th>
                                        					<th>asdf</th>
                                        					<th>asdfasdf</th>
                                        					<th>asdf</th>
                                        					<th>sadfsadfds</th>
                                        				</tr>
                                        			</thead>

                                        			<tbody>
                                        				<tr>
                                        					<td></td>
                                        					<td></td>
                                        					<td></td>
                                        					<td></td>
                                        					<td></td>
                                        				</tr>
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

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.create_drip_campaign')}}</h4>
            </div>

            <div class="modal-body">

            </div>

	        <div class="modal-footer">
	            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
	        </div>
	    </div>
    </div>
</div>

<!-- Add Provider Modal -->
<div class="modal fade" id="addProviderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_provider')}}</h4>
            </div>

            <div class="modal-body">
				<form action="#" method="post" class="form add_provider">
					<div class="form-group">
						<label>{{__('tools.provider')}}</label>
						<select name="provider" id="provider" class="form-control provider">
							<option value="">Select One</option>
							@foreach($providers as $provider)
								<option value="{{$provider}}">{{$provider}}</option>
							@endforeach
						</select>
					</div>

					<div class="form-group">
						<label>{{__('tools.provider_name')}}</label>
						<input type="text" class="form-control name" name="name">
					</div>

					<div class="form-group">
						<label>{{__('tools.username')}}</label>
						<input type="text" class="form-control username" name="username">
					</div>

					<div class="form-group">
						<label>{{__('tools.password')}}</label>
						<input type="password" class="form-control password" name="password">
					</div>

					<input type="submit" class="btn btn-primary" value="{{__('general.submit')}}">

					<div class="alert alert-success hidetilloaded">{{__('tools.provider_added_success')}}</div>
					<div class="alert alert-danger hidetilloaded"></div>
				</form>
            </div>

	        <div class="modal-footer">
	        	<a href="#" class="test_connection btn btn-warning">Test Connection</a>
	            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
	        </div>
	    </div>
    </div>
</div>

<!-- Upload Template Modal -->
<div class="modal fade" id="uploadTemplateModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.upload_template')}}</h4>
            </div>

            <div class="modal-body">

				<form action="#" method="post" class="form">
					<div class="form-group mt20">
						<label class="btn btn-secondary" for="email_template">
							<input id="email_template" type="file" style="display:none" 
							onchange="$('#upload-file-info').html(this.files[0].name)">
							{{__('tools.select_template')}}
						</label>
						<span class='label label-info' id="upload-file-info"></span>
					</div>

					<button type="submit" class="btn btn-primary upload_email_template"><i class="fas fa-file-upload"></i> {{__('tools.upload')}}</button>
				</form>
            </div>

	        <div class="modal-footer">
	            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
	        </div>
	    </div>
    </div>
</div>

<!-- Delete Provider Modal -->
<div class="modal fade" id="deleteProviderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_provider')}}</h4>
            </div>

            <div class="modal-body">

            </div>

	        <div class="modal-footer">
	            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
	        </div>
	    </div>
    </div>
</div>

@endsection