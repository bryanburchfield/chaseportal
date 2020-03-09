@extends('layouts.master')
@section('title', __('widgets.admin'))

@section('content')
<div class="preloader"></div>
<div class="wrapper">

	@include('shared.admin_sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt20">
				<div class="row">
					<div class="col-sm-12">
						<div class="mt20">
							<ul class="nav nav-tabs tabs">

								<li class="active"><a  href="#new_user" data-toggle="tab">{{__('users.add_users')}}</a></li>
								<li><a href="#edit_user" data-toggle="tab">{{__('users.edit_users')}}</a></li>
								@can('accessSuperAdmin')
									<li><a href="#demo_user" data-toggle="tab">Demo Users</a></li>
								@endcan
							</ul>

							<div class="tab-content">
								<div class="tab-pane active mt30" id="new_user">
									@include('shared.clientform', ['mode' => 'add'])
									@include('shared.dialerlist', ['mode' => 'add'])
								</div>

								@can('accessSuperAdmin')
									<div class="tab-pane mt30" id="demo_user">
										<div class="col-sm-5 mb0 card">
											<h2 class="page_heading">Add Demo User</h2>
											@include('shared.demouser_form', ['mode' => 'add'])
										</div>

										<div class="col-sm-7 mb0">
											<div class="table-responsive demo_user_table_holder nobdr">
												<table class="table demo_user_table table-striped">
													<thead>
														<tr>
															<th>Name</th>
															<th>Phone</th>
															<th>Link</th>
															<th>Expires</th>
															<th>Edit</th>
															<th>Delete</th>
														</tr>
													</thead>

													<tbody>
														@foreach($demo_users as $user)
															<tr id="user{{$user->id}}" data-id="{{$user->id}}">
																<td>{{$user->name}}</td>
																<td>{{$user->phone}}</td>
																<td><a data-toggle="tooltip"  title="Link Copied!" href="#" class="getAppToken">{{url('/')}}/demo/{{$user->app_token}}<span class="url_token"></span></a></td>
																@if (strtotime($user->expiration) < time())
																	<td class="bg-danger">
																@else
																	<td>
																@endif
																{{date('m-d-Y',strtotime($user->expiration))}}</td>
																<td><a class="demo_user_modal_link edit_demo_user" href="#" data-toggle="modal" data-target="#demoUserModal" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="fas fa-user-edit"></i></a></td>
																<td><a class="demo_user_modal_link remove_user" data-toggle="modal" data-target="#deleteUserModal" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="fa fa-trash-alt"></i></a></td>
															</tr>
														@endforeach
													</tbody>
												</table>
											</div>
										</div>
									</div>
								@endcan

								<div class="tab-pane mt30" id="edit_user">
									@include('shared.clientform', ['mode' => 'edit'])
									@include('shared.dialerlist', ['mode' => 'edit'])
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

@can('accessSuperAdmin')
	<!-- EDIT Demo User Modal -->
	<div class="modal fade" id="demoUserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Edit Demo User</h4>
				</div>
				<div class="modal-body">
					@include('shared.demouser_form', ['mode' => 'edit'])
					<input type="hidden" class="demouser_id" name="demouser_id" value="">
					<input type="hidden" class="demouser_name" name="demouser_name" value="">
				</div>
			</div>
		</div>
	</div>
@endcan

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('users.confirm_delete_user')}}</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="user_id" name="user_id" value="">
                <input type="hidden" class="name" name="name" value="">
               <h3>{{__('users.are_you_sure')}} <span class="username"></span>?</h3>
            </div>
	        <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('users.cancel')}}</button>
	            <button type="button" class="btn btn-danger remove_recip">{{__('users.delete_user')}}</button>
	        </div>
	    </div>
    </div>
</div>

<!-- User Links Modal -->
<div class="modal fade" id="userLinksModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">User Links</h4>
            </div>
            <div class="modal-body user_links_modal">
                <input type="hidden" class="user_id" name="user_id" value="">
                <input type="hidden" class="name" name="name" value="">
                <input type="hidden" class="app_token" name="app_token" value="">
                <h3 class="mb10"><span class="username mb20"></span></h3>
            	<p>{{__('users.double_click')}}.</p><br>
            	<a data-toggle="tooltip"  title={{__('users.link_copied')}} href="#" class="getAppToken">{{url('/')}}/agentdashboard/api/<span class="url_token"></span>/(#Rep#)</a>
				<a data-toggle="tooltip"  title={{__('users.link_copied')}} href="#" class="getAppToken">{{url('/')}}/agentoutbounddashboard/api/<span class="url_token"></span>/(#Rep#)</a>
				<a data-toggle="tooltip"  title={{__('users.link_copied')}} href="#" class="getAppToken">{{url('/')}}/inbounddashboard/api/<span class="url_token"></span></a>
				<a data-toggle="tooltip"  title={{__('users.link_copied')}} href="#" class="getAppToken">{{url('/')}}/outbounddashboard/api/<span class="url_token"></span></a>
				<a data-toggle="tooltip"  title={{__('users.link_copied')}} href="#" class="getAppToken">{{url('/')}}/leaderdashboard/api/<span class="url_token"></span></a>
				<a data-toggle="tooltip"  title={{__('users.link_copied')}} href="#" class="getAppToken">{{url('/')}}/trenddashboard/api/<span class="url_token"></span></a>

            </div>
	        <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        </div>
	    </div>
    </div>
</div>


@endsection