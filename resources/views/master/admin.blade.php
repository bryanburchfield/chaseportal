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
					<div class="col-sm-6">
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
								{!! Form::select("tz", $timezone_array, null, ["class" => "form-control", 'id'=> 'tz']) !!}
							</div>

							<div class="form-group">
								{!! Form::label('user_type', 'Type') !!}
								{!! Form::select("user_type", [''=> 'Choose One','client'=>'Client', 'admin'=>'Chase Admin'], null, ["class" => "form-control", 'id'=> 'user_type']) !!}
							</div>

							<div class="form-group">
								{!! Form::label('db', 'Database') !!}
								{!! Form::select("db", $dbs, null, ["class" => "form-control", 'id'=> 'db']) !!}
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

					<div class="col-sm-6">

						<h2 class="page_heading">All Users</h2>

						<div class="users">
							@foreach($users as $user)
								<p id="user{{$user->id}}">{{$user->group_id}} - <span class="user_name">{{$user->name}}</span><a data-toggle="modal" data-target="#deleteRecipModal" class="remove_user" href="#" data-user="1"><i class="glyphicon glyphicon-remove-sign"></i></a></p>
							@endforeach
						</div>
						
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>
@endsection