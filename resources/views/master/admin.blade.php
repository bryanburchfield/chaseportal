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
<<<<<<< HEAD
=======
					<div class="col-sm-6">
						<h2 class="page_heading">New User</h2>
						{!! Form::open(['method'=>'POST', 'url'=>'/dashboards/add_user', 'class'=>'form well']) !!}
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
>>>>>>> 2b59da349b3a87d8b0bda72b4c026a33799efe38

					{{-- <div class="col-sm-6">

						<h2 class="page_heading">All Users</h2>

						<div class="users">
							@foreach($users as $user)
								<p id="user{{$user->id}}">{{$user->group_id}} - <span class="user_name">{{$user->name}}</span><a data-toggle="modal" data-target="#deleteRecipModal" class="remove_user" href="#" data-userid="{{$user->id}}" data-username="{{$user->name}}"><i class="glyphicon glyphicon-remove-sign"></i></a></p>
							@endforeach
						</div>
					</div> --}}
					<div class="col-sm-12">
						<div class="mt20">	
							<ul class="nav nav-tabs">
								<li class="active"><a  href="#new_user" data-toggle="tab">Add Users</a></li>
								<li><a href="#edit_user" data-toggle="tab">Edit Users</a></li>
								<li><a href="#cdr_lookup" data-toggle="tab">CDR Lookup</a></li>
							</ul>

							<div class="tab-content ">
								<div class="tab-pane active mt30" id="new_user">
					        		<div class="col-sm-6 mb0 pl0">
        								<h2 class="page_heading">New User</h2>
        								{!! Form::open(['method'=>'POST', 'url'=>'/master/add_user', 'class'=>'form well']) !!}
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