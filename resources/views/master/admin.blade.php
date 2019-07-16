@extends('layouts.master')
@section('title', 'Admin')

@section('content')
<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.reportnav')
		
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
					        		<div class="col-sm-6 mb0 pl0">
        								<h2 class="page_heading">New User</h2>
        								{!! Form::open(['method'=>'POST', 'url'=>'/dashboards/add_user', 'class'=>'form well add_user']) !!}
        									<div class="form-group">
        										{!! Form::label('group_id', 'Group ID') !!}
        										{!! Form::text('group_id', $group_id, ['class'=>'form-control group_id', 'required'=>true]) !!}
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
        										{!! Form::label('user_type', 'Type') !!}
        										{!! Form::select("user_type", [''=> 'Choose One','client'=>'Client', 'admin'=>'Chase Admin'], null, ["class" => "form-control", 'id'=> 'user_type', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('db', 'Database') !!}
        										{!! Form::select("db", $dbs, null, ["class" => "form-control", 'id'=> 'db', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('additional_db', 'Database 2') !!}
        										{!! Form::select("additional_db", $dbs, null, ["class" => "form-control", 'id'=> 'additional_db']) !!}
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

				    				<div class="col-sm-6 mbp0 pr0">
				    					<h2 class="page_heading">All Users</h2>
				    					
				    					<div class="users">

				    						<table class="table table-responsive table-striped">
				    							<thead>
				    								<tr>
				    									<th>User</th>
				    									<th>Edit</th>
				    									<th>Delete</th>
				    								</tr>
				    							</thead>

				    							<tbody>
				    								@foreach($users as $user)
				    									<tr id="user{{$user->id}}" data-id="{{$user->id}}">
			    										<td>{{$user->group_id}} -  {{$user->name}}</td>
			    										<td><a href="{{$user->id}}" class="edit_user"><i class="fas fa-user-edit"></i></a></td>
			    										<td><a data-toggle="modal" data-target="#deleteUserModal" class="remove_user" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="glyphicon glyphicon-remove-sign"></i></a></td>
				    								@endforeach
				    								
				    							</tbody>
				    						</table>
				    					</div>
				    				</div>
								</div>		

								<div class="tab-pane mt30" id="edit_user">
					         		<div class="col-sm-6 pl0 mbp0">
			         					<h2 class="page_heading">Edit User</h2>
			         					
			         					{!! Form::open(['method'=>'POST', 'url'=>'/dashboards/edit_user', 'class'=>'form well edit_user']) !!}
        									<div class="form-group">
        										{!! Form::label('group_id', 'Group ID') !!}
        										{!! Form::text('group_id', $group_id, ['class'=>'form-control group_id', 'required'=>true]) !!}
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
        										{!! Form::label('user_type', 'Type') !!}
        										{!! Form::select("user_type", [''=> 'Choose One','client'=>'Client', 'admin'=>'Chase Admin'], null, ["class" => "form-control", 'id'=> 'user_type', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('db', 'Database') !!}
        										{!! Form::select("db", $dbs, null, ["class" => "form-control", 'id'=> 'db', 'required'=>true]) !!}
        									</div>

        									<div class="form-group">
        										{!! Form::label('additional_db', 'Database 2') !!}
        										{!! Form::select("additional_db", $dbs, null, ["class" => "form-control", 'id'=> 'additional_db']) !!}
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

			         				<div class="col-sm-6 mbp0 pr0">
				    					<h2 class="page_heading">All Users</h2>
				    					
				    					<div class="users">

				    						<table class="table table-responsive table-striped">
				    							<thead>
				    								<tr>
				    									<th>User</th>
				    									<th>Edit</th>
				    									<th>Delete</th>
				    								</tr>
				    							</thead>

				    							<tbody>
				    								@foreach($users as $user)
				    									<tr id="user{{$user->id}}" data-id="{{$user->id}}">
			    										<td>{{$user->group_id}} -  {{$user->name}}</td>
			    										<td><a href="{{$user->id}}" class="edit_user"><i class="fas fa-user-edit"></i></a></td>
			    										<td><a data-toggle="modal" data-target="#deleteUserModal" class="remove_user" href="#" data-name="{{$user->name}}" data-user="{{$user->id}}"><i class="glyphicon glyphicon-remove-sign"></i></a></td>
				    								@endforeach
				    								
				    							</tbody>
				    						</table>
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

				        		    <!-- <div class="col-sm-12"> -->
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
					        		<!-- </div> -->
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
<div class="modal fade" id="deleteRecipModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Confirm User Removal</h4>
            </div>
            <div class="modal-body">
                
               <h3>Are you sure you want to delete <span class="username"></span>?</h3>
            </div>
	        <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
	            {!! Form::open(['method'=> 'POST', 'url'=> 'dashboards/delete_user']) !!}
		            {!! Form::hidden('userid', null, ['id'=>'userid']) !!}
		            {!! Form::hidden('username', null, ['id'=>'username']) !!}
		            {!! Form::submit('Delete User', ['class'=>'btn btn-danger']) !!}
				{!! Form::close() !!}
	        </div>
	    </div>
    </div>
</div>

@endsection