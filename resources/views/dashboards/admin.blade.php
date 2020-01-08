@extends('layouts.master')
@section('title', __('widgets.admin'))

@section('content')
<div class="preloader"></div>
<?php

?>
<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt20">
				<div class="row">
					<div class="col-sm-12">
						<div class="mt20">
							<ul class="nav nav-tabs tabs">

								<li class="active"><a  href="#new_user" data-toggle="tab">Add Clients</a></li>
								<li><a href="#edit_user" data-toggle="tab">Edit Clients</a></li>
								<li><a href="#demo_user" data-toggle="tab">Demo Clients</a></li>
								<li><a href="#cdr_lookup" data-toggle="tab">CDR Lookup</a></li>
								<li><a href="#settings" data-toggle="tab">My Settings</a></li>
							</ul>

							<div class="tab-content">
								<div class="tab-pane active mt30" id="new_user">
									@include('shared.clientform', ['mode' => 'add'])
									@include('shared.dialerlist', ['mode' => 'add'])
								</div>

								<div class="tab-pane mt30" id="demo_user">
									<div class="col-sm-5 mb0 card">
										<h2 class="page_heading">Add Demo Client</h2>
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
															<td>{{date('m-d-Y',strtotime($user->expiration))}}</td>
															<td><a class="demo_user_modal_link edit_demo_user" href="#" data-toggle="modal" data-target="#demoUserModal" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="fas fa-user-edit"></i></a></td>
															<td><a class="demo_user_modal_link remove_user" data-toggle="modal" data-target="#deleteUserModal" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="fa fa-trash-alt"></i></a></td>
														</tr>
													@endforeach
												</tbody>
											</table>
										</div>
									</div>
								</div>

								<div class="tab-pane mt30" id="edit_user">
									@include('shared.clientform', ['mode' => 'edit'])
									@include('shared.dialerlist', ['mode' => 'edit'])
								</div>

								<div class="tab-pane" id="cdr_lookup">
									<div class="report_filters card col-sm-12">
										<h2 class="page_heading">CDR Lookup</h2>
										<form action="#" method="POST" class="form cdr_lookup_form" name="cdr_lookup_form"
											id="">
											<div class="row">

												<div class="col-sm-4">
													<div class="form-group">
														<label>Phone #</label>
														<input type="tel" name="phone" id="phone" class="form-control"
															required><br>
														<label class="radio-inline"><input class="search_type" type="radio"
																name="search_type" value="number_dialed" checked>Number
															Dialed</label>
														<label class="radio-inline"><input class="search_type" type="radio"
																name="search_type" value="caller_id">Caller ID</label>
													</div>
												</div>

												<div class="col-sm-4">
													<div class="form-group">
														<label>From</label>
														<div class='input-group date '>
															<input type='text' readonly="true" name="fromdate"
																class="form-control datetimepicker fromdate" required
																value="" />
															<span class="input-group-addon">
																<span class="glyphicon glyphicon-calendar">
																</span>
															</span>
														</div>
													</div>
												</div>

												<div class="col-sm-4">
													<div class="form-group">
														<label>To</label>
														<div class='input-group date '>
															<input type='text' readonly="true" name="todate"
																class="form-control datetimepicker todate" required value="" />
															<span class="input-group-addon">
																<span class="glyphicon glyphicon-calendar">
																</span>
															</span>
														</div>
													</div>
												</div>
											</div>

											<div class="alert alert-danger report_errors"></div>
											<input type="submit" class="btn btn-primary mb0" value="Search">
										</form>
									</div> <!-- end report_filters -->

									<div class="table-responsive cdr_table ">
										<table class="cdr_results_table table table-hover reports_table" id="cdr_dataTable">
											<thead>
												<tr role="row">
													<th>ID</th>
													<th>Server</th>
													<th>Attempt</th>
													<th>Call Date</th>
													<th>Call Status</th>
													<th>Call Type</th>
													<th>Caller ID</th>
													<th>Campaign</th>
													<th>Date</th>
													<th>Duration</th>
													<th>Group ID</th>
													<th>Lead ID</th>
													<th>Phone</th>
													<th>Rep</th>
													<th>Subcampaign</th>
												</tr>
											</thead>

											<tbody>

											</tbody>
										</table>
									</div>
								</div>

								<div class="tab-pane mt30" id="settings">
									<div class="col-sm-6 mb20 card">
										<h2 class="page_heading">Edit Myself</h2>

										{!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'form
										edit_myself']) !!}
										<div class="form-group">
											{!! Form::label('group_id', 'Group ID') !!}
											{!! Form::text('group_id', Auth::user()->group_id, ['class'=>'form-control
											group_id', 'required'=>true]) !!}
										</div>


										<div class="form-group">
											{!! Form::label('db', 'Database') !!}
											{!! Form::select("db", $dbs, Auth::user()->db, ["class" => "form-control", 'id'=>
											'db',
											'required'=>true]) !!}
										</div>

										{!! Form::hidden('id', null, ['class'=>'user_id']) !!}

										{!! Form::submit('Update User', ['class'=>'btn btn-primary mb0'] ) !!}

										<br><br>

										@if($errors->any())
										<div class="alert alert-danger">
											@foreach($errors->all() as $e)
											{{ $e }}
											@endforeach
										</div>
										@endif

										{!! Form::close() !!}
									</div>

									@include('shared.settings')
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

<!-- EDIT Demo User Modal -->
<div class="modal fade" id="demoUserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Edit Demo Client</h4>
            </div>
            <div class="modal-body">
            	@include('shared.demouser_form', ['mode' => 'edit'])
                <input type="hidden" class="demouser_id" name="demouser_id" value="">
				<input type="hidden" class="demouser_name" name="demouser_name" value="">
            </div>
	    </div>
    </div>
</div>

<!-- Delete Recipient Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Confirm Client Removal</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="user_id" name="user_id" value="">
                <input type="hidden" class="name" name="name" value="">
               <h3>Are you sure you want to delete <span class="username"></span>?</h3>
            </div>
	        <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
	            <button type="button" class="btn btn-danger remove_recip">Delete Client</button>
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
                <h4 class="modal-title" id="myModalLabel">Client Links</h4>
            </div>
            <div class="modal-body user_links_modal">
                <input type="hidden" class="user_id" name="user_id" value="">
                <input type="hidden" class="name" name="name" value="">
                <input type="hidden" class="app_token" name="app_token" value="">
                <h3 class="mb10"><span class="username mb20"></span></h3>
            	<p>Double click a link below to copy.</p><br>
            	<a data-toggle="tooltip"  title="Link Copied!" href="#" class="getAppToken">{{url('/')}}/agentdashboard/api/<span class="url_token"></span>/(#Rep#)</a>
				<a data-toggle="tooltip"  title="Link Copied!" href="#" class="getAppToken">{{url('/')}}/agentoutbounddashboard/api/<span class="url_token"></span>/(#Rep#)</a>
				<a data-toggle="tooltip"  title="Link Copied!" href="#" class="getAppToken">{{url('/')}}/admindashboard/api/<span class="url_token"></span></a>
				<a data-toggle="tooltip"  title="Link Copied!" href="#" class="getAppToken">{{url('/')}}/adminoutbounddashboard/api/<span class="url_token"></span></a>
				<a data-toggle="tooltip"  title="Link Copied!" href="#" class="getAppToken">{{url('/')}}/leaderdashboard/api/<span class="url_token"></span></a>
				<a data-toggle="tooltip"  title="Link Copied!" href="#" class="getAppToken">{{url('/')}}/trenddashboard/api/<span class="url_token"></span></a>

            </div>
	        <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        </div>
	    </div>
    </div>
</div>


@endsection