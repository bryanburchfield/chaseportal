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
						<div class="mt30" id="notifications">
							<div class="col-sm-6 mb20 card">
								<h2 class="page_heading">Create Notification</h2>

								{!! Form::open(['method'=>'POST', 'action' => 'FeatureMessageController@createMessage', 'class'=>'form
								create_notification']) !!}
								<div class="form-group">
									{!! Form::label('title', 'Title') !!}
									{!! Form::text('title', null, ['class'=>'form-control
									notification_title', 'required'=>true]) !!}
								</div>


								<div class="form-group">
									{!! Form::label('body', 'Body') !!}
									{!! Form::textarea("body", null, ["class" => "form-control notification_body", 'required'=>true]) !!}
								</div>


								<button type="submit" class="btn btn-primary mt30">{{__('general.submit')}}</button>

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
								<table class="table">
									<thead>
										<tr>
											<th>ID</th>
											<th>Title</th>
											<th>Body</th>
											<th>Delete</th>
										</tr>
									</thead>

									<tbody>
										@foreach($feature_messages as $msg)
											<tr>
												<td>{{$msg->id}}</td>
												<td>{{$msg->title}}</td>
												<td>{{$msg->body}}</td>
												<td><a data-toggle="modal" data-target="#deleteUserModal" class="remove_user" href="#" data-name="newtest" data-user="68"><i class="fa fa-trash-alt"></i></a></td>
											</tr>
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

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')
@endsection