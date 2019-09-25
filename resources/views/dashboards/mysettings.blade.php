@extends('layouts.master')
@section('title', 'Admin')

@section('content')
<div class="preloader"></div>




<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-full mt20">
		    <div class="row">

				<div class="col-sm-12">
					<h2>My settings</h2>

					<div class="col-sm-6 card">
						{!! Form::open(['method'=>'POST', 'class'=>'form user_settings', 'name' => 'user_settings']) !!}

							<div class="form-group">
								{!! Form::label('name', 'Name') !!}
								{!! Form::text('name', $user->name, ['class'=>'form-control name', 'required'=>true]) !!}
							</div>

							<div class="form-group">
								{!! Form::label('email', 'Email') !!}
								{!! Form::email('email', $user->email, ['class'=>'form-control email', 'required'=>true]) !!}
							</div>

							<div class="form-group">
								{!! Form::label('current_password', 'Current Password') !!}
								{!! Form::password('current_password', ['class'=>'form-control current_password', 'required'=>true]) !!}
							</div>

							<div class="form-group">
								{!! Form::label('new_password', 'New Password') !!}
								{!! Form::password('new_password', ['class'=>'form-control new_password', 'required'=>true]) !!}
							</div>

							<div class="form-group">
								{!! Form::label('conf_password', 'Confirm Password') !!}
								{!! Form::password('conf_password', ['class'=>'form-control conf_password', 'required'=>true]) !!}
							</div>

							{!! Form::hidden('id', $user->id, ['id'=>'user_id']) !!}

							{!! Form::submit('Update', ['class'=>'btn btn-primary mb0'] ) !!}

								@if($errors->any())

	                                <div class="alert alert-danger mt20">
	                                    @foreach($errors->all() as $e)
	                                        <li>{{ $e }}</li>
	                                    @endforeach
	                                </div>
								@endif

								@if(isset($_POST['user_settings']) && empty($errors->any()))
									<div class="alert alert-success mt20">User successfully updated</div>
								@endif

							{!! Form::close() !!}

					</div>
				</div>
		    </div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection