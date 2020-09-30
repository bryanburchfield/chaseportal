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
						<div class="mt30" id="settings">
							<div class="col-sm-6 mb20 card">
								<h2 class="page_heading">Edit Myself</h2>

								{!! Form::open(['method'=>'POST', 'url'=>'#', 'class'=>'form
								edit_myself fc_style']) !!}
								<div class="form-group">
									{!! Form::label('group_id', 'Group ID') !!}
									{!! Form::text('group_id', Auth::user()->group_id, ['class'=>'form-control
									group_id', 'required'=>true]) !!}
								</div>


								<div class="form-group">

									{!! Form::label('dialer_id', 'Database') !!}
									{!! Form::select("dialer_id", $dbs, Auth::user()->dialer->id, ["class" => "form-control", 'id'=>'db', 'required'=>true]) !!}
								</div>

								<div class="form-group">
								    {!! Form::label('tz', __('users.timezone')) !!}
								    {!! Form::select("tz", $timezone_array, Auth::user()->tz, ["class" => "form-control", 'id'=> 'tz', 'required'=>true]) !!}
								</div>

								{!! Form::hidden('id', null, ['class'=>'user_id']) !!}

								<button type="submit" class="btn btn-primary mt30 add_btn_loader">{{__('general.update')}}</button>

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
						</div>

						@include('shared.settings')
					</div>
				</div>
			</div>
		</div>
	</div>
	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')
@endsection