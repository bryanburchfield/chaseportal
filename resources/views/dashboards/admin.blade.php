@extends('layouts.master')
@section('title', __('widgets.admin'))

@section('content')
<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt20">
				<div class="row">
					<div class="col-sm-12">
						<div class="mt20">
							<ul class="nav nav-tabs">
								<li class="active"><a  href="#new_user" data-toggle="tab">Add Users</a></li>
								<li><a href="#edit_user" data-toggle="tab">Edit Users</a></li>
								<li><a href="#cdr_lookup" data-toggle="tab">CDR Lookup</a></li>
							</ul>

							<div class="tab-content">
								<div class="tab-pane active mt30" id="new_user">
					        		<div class="col-sm-6 mb0 card">
        								<h2 class="page_heading">New User</h2>

        								{!! Form::open(['method'=>'POST', 'url'=>'/dashboards/add_user', 'class'=>'form add_user']) !!}
        									<div class="form-group">
        										{!! Form::label('group_id', 'Group ID') !!}
        										{!! Form::text('group_id', null, ['class'=>'form-control group_id', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('name', 'Name') !!}
        										{!! Form::text('name', null, ['class'=>'form-control name', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('email', 'Email') !!}
        										{!! Form::email('email', null, ['class'=>'form-control email', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('tz', 'Timezone') !!}
        										{!! Form::select("tz", $timezone_array, null, ["class" => "form-control", 'id'=> 'tz', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('db', 'Database') !!}
        										{!! Form::select("db", $dbs, null, ["class" => "form-control", 'id'=> 'db', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('additional_dbs', 'Database 2') !!}
        										{!! Form::select("additional_dbs", $dbs, null, ["class" => "form-control", 'id'=> 'additional_dbs']) !!}
        									</div>

        									{!! Form::submit('Create User', ['class'=>'btn btn-primary mb0'] ) !!}

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

				    				<div class="col-sm-6 pr0 mbmt50 mbp0">
				    					<h2 class="page_heading mb0">All Users</h2>

				    					<div class="users">

											<div class="panel-group" id="add_accordion" role="tablist" aria-multiselectable="true">

												@foreach (App\Dialer::orderBy('dialer_numb')->get() as $dialer)
													@php
													$db = sprintf("%02d", $dialer->dialer_numb);
													@endphp

												    <div class="panel panel-default">
												        <div class="panel-heading" role="tab" id="add_heading{{$db}}">
												            <h4 class="panel-title">
												                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#add_accordion" href="#add_dialer{{$db}}" aria-expanded="false" aria-controls="add_dialer{{$db}}">
												                Dialer {{$db}}
												                </a>
												            </h4>
												        </div>
												        <div id="add_dialer{{$db}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="add_heading{{$db}}">
												            <div class="panel-body">

												            	<table class="table table-responsive table-striped">
												            		<thead>
												            			<tr>
												            				<th>User</th>
												            				<th>Links</th>
												            				<th>Edit</th>
												            				<th>Delete</th>
												            			</tr>
												            		</thead>

												            		<tbody>
												            	@foreach($users as $user)
												            		@php
												            			$user_db = substr($user['db'], -2);
												            		@endphp
												            		@if($user_db == $db)

												            			<tr id="user{{$user->id}}" data-id="{{$user->id}}">
												            			<td>{{$user->group_id}} - {{$user->name}}</td>
												            			<td><a data-toggle="modal" data-target="#userLinksModal" class="user_links" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}" data-token="{{$user->app_token}}"><i class="fas fa-link"></i></a></td>
												            			<td><a data-dialer="{{$db}}" href="{{$user->id}}" class="edit_user"><i class="fas fa-user-edit"></i></a></td>
												            			<td><a data-toggle="modal" data-target="#deleteUserModal" class="remove_user" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="glyphicon glyphicon-remove-sign"></i></a></td>
												            		@endif

												            	@endforeach

													            	</tbody>
													            </table>
												            </div>
												        </div>
												    </div>

											    @endforeach
											</div>
				    					</div>
				    				</div>

								</div>

								<div class="tab-pane mt30" id="edit_user">
					         		<div class="col-sm-6 card">
			         					<h2 class="page_heading">Edit User</h2>
			         					{!! Form::open(['method'=>'POST', 'url'=>'/dashboards/edit_user', 'class'=>'form edit_user']) !!}
        									<div class="form-group">
        										{!! Form::label('group_id', 'Group ID') !!}
        										{!! Form::text('group_id', null, ['class'=>'form-control group_id', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('name', 'Name') !!}
        										{!! Form::text('name', null, ['class'=>'form-control name', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('email', 'Email') !!}
        										{!! Form::email('email', null, ['class'=>'form-control email', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('tz', 'Timezone') !!}
        										{!! Form::select("tz", $timezone_array, null, ["class" => "form-control", 'id'=> 'tz', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('db', 'Database') !!}
        										{!! Form::select("db", $dbs, null, ["class" => "form-control", 'id'=> 'db', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('additional_dbs', 'Database 2') !!}
        										{!! Form::select("additional_dbs", $dbs, null, ["class" => "form-control", 'id'=> 'additional_dbs']) !!}
        									</div>

        									{!! Form::hidden('id', null, ['id'=>'user_id']) !!}

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

			         				<div class="col-sm-6 mbp0 pr0">
				    					<h2 class="page_heading mb0">All Users</h2>

				    					<div class="users">

											<div class="panel-group" id="edit_accordion" role="tablist" aria-multiselectable="true">

												@foreach (App\Dialer::orderBy('dialer_numb')->get() as $dialer)
													@php
													$db = sprintf("%02d", $dialer->dialer_numb);
													@endphp

												    <div class="panel panel-default">
												        <div class="panel-heading" role="tab" id="edit_heading{{$db}}">
												            <h4 class="panel-title">
												                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#edit_accordion" href="#edit_dialer{{$db}}" aria-expanded="false" aria-controls="edit_dialer{{$db}}">
												                Dialer {{$db}}
												                </a>
												            </h4>
												        </div>
												        <div id="edit_dialer{{$db}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="edit_heading{{$db}}">
												            <div class="panel-body">
												            	<table class="table table-responsive table-striped">
												            		<thead>
												            			<tr>
												            				<th>User</th>
												            				<th>Links</th>
												            				<th>Edit</th>
												            				<th>Delete</th>
												            			</tr>
												            		</thead>

												            		<tbody>
												            	@foreach($users as $user)
												            		@php
												            			$user_db = substr($user['db'], -2);
												            		@endphp

												            		@if($user_db == $db)
												            			<tr id="user{{$user->id}}" data-id="{{$user->id}}">
												            			<td>{{$user->group_id}} - {{$user->name}}</td>
												            			<td><a data-toggle="modal" data-target="#userLinksModal" class="user_links" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}" data-token="{{$user->app_token}}"><i class="fas fa-link"></i></a></td>
												            			<td><a data-dialer="{{$db}}" href="{{$user->id}}" class="edit_user"><i class="fas fa-user-edit"></i></a></td>
												            			<td><a data-toggle="modal" data-target="#deleteUserModal" class="remove_user" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="glyphicon glyphicon-remove-sign"></i></a></td>
												            		@endif

												            	@endforeach

													            	</tbody>
													            </table>
												            </div>
												        </div>
												    </div>

											    @endforeach
											</div>
				    					</div>
				    				</div>
								</div>

					        	<div class="tab-pane" id="cdr_lookup">
					        		<div class="report_filters card col-sm-12">
					        			<h2 class="page_heading">CDR Lookup</h2>
					        		    <form action="#" method="POST" class="form cdr_lookup_form" name="cdr_lookup_form" id="">
					        		        <div class="row">

					        		            <div class="col-sm-4">
					        		                <div class="form-group">
					        		                    <label>Phone #</label>
					        		                    <input type="tel" name="phone" id="phone" class="form-control" required><br>
					        		                    <label class="radio-inline"><input class="search_type" type="radio" name="search_type" value="number_dialed" checked>Number Dialed</label>
					        		                    <label class="radio-inline"><input class="search_type" type="radio" name="search_type" value="caller_id">Caller ID</label>
					        		                </div>
					        		            </div>

					        		            <div class="col-sm-4">
					        		                <div class="form-group">
					        		                    <label>From</label>
					        		                    <div class='input-group date '>
					        		                        <input type='text' readonly="true" name="fromdate" class="form-control datetimepicker fromdate" required value=""/>
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
					        		                        <input type='text' readonly="true" name="todate" class="form-control datetimepicker todate" required value=""/>
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
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

<!-- Delete Recipient Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Confirm Recipient Removal</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="user_id" name="user_id" value="">
                <input type="hidden" class="name" name="name" value="">
               <h3>Are you sure you want to delete <span class="username"></span>?</h3>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger remove_recip">Delete User</button>
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