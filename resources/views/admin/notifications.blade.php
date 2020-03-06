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
								<h2 class="page_heading">{{ empty($feature_message->id) ? 'Create' : 'Edit' }} Notification</h2>

								{!! Form::open(['method'=>'POST', 'action' => 'FeatureMessageController@saveMessage', 'class'=>'form
								create_notification']) !!}
								<div class="form-group">
									{!! Form::label('title', 'Title') !!}
									{!! Form::text('title', $feature_message->title, ['class'=>'form-control
									notification_title', 'required'=>true]) !!}
								</div>

								{!! Form::hidden('id', $feature_message->id, ['class'=>'form-control id']) !!}

								<div class="form-group">
									{!! Form::label('body', 'Body') !!}
									{!! Form::textarea("body", $feature_message->body, ["class" => "form-control notification_body", 'required'=>true]) !!}
								</div>

								<div class="form-group">
									<label class="checkbox-inline">
										<input type="checkbox" name="active" value="1" {{ $feature_message->active ? 'checked' : '' }}> Publish Now?
									</label>
								</div>

								@if(!empty($feature_message->id))
									<a href="{{url('dashboards/admin/notifications/')}}" class="cancel btn btn-secondary">Cancel</a>
								@endif

								<button type="submit" class="btn btn-primary mt10">{{__('general.submit')}}</button>

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
											<th>Published</th>
											<th>ID</th>
											<th>Title</th>
											<th>Body</th>
											<th>Edit</th>
											<th>Delete</th>
										</tr>
									</thead>

									<tbody>
										@foreach($feature_messages as $msg)
											<tr>
												<td><input type="checkbox" class="checkbox published" name="published" data-id="{{$msg->id}}" {{$msg->active ? 'checked' : ''}}></td>
												<td>{{$msg->id}}</td>
												<td>{{$msg->title}}</td>
												<td>{{ \Illuminate\Support\Str::limit($msg->body, 40, $end='...') }}</td>
												<td><a class="edit_msg" href="{{action('FeatureMessageController@editMessage', [$msg->id])}}"><i class="fas fa-edit"></i></a></td>
												<td><a data-toggle="modal" data-target="#deleteMsgModal" class="remove_msg remove_msg_modal" href="#" data-title="{{$msg->title}}" data-id="{{$msg->id}}"><i class="fa fa-trash-alt"></i></a></td>
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

<!-- Delete Msg Modal -->
<div class="modal fade" id="deleteMsgModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_msg')}}</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="id" name="id" value="">
            	<h3>{{__('users.are_you_sure')}} <span class="title"></span>?</h3>
            </div>
	        <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('users.cancel')}}</button>
	            <button type="button" class="btn btn-danger delete_msg">{{__('users.delete')}}</button>
	        </div>
	    </div>
    </div>
</div>

@include('shared.reportmodal')
@endsection