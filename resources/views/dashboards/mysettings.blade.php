@extends('layouts.master')
@section('title', __('widgets.settings'))

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
						<h2>{{__('general.my_settings')}}</h2>

						<div class="col-sm-6 card">
							{!! Form::open(['method'=>'POST', 'class'=>'form user_settings', 'name' => 'user_settings']) !!}

								<div class="form-group">
									{!! Form::label('name', __('general.full_name')) !!}
									{!! Form::text('name', Auth::user()->name, ['class'=>'form-control name', 'required'=>true]) !!}
								</div>

								<div class="form-group">
									{!! Form::label('email', __('general.email')) !!}
									{!! Form::email('email', Auth::user()->email, ['class'=>'form-control email', 'required'=>true]) !!}
								</div>

								<div class="form-group">
									{!! Form::label('current_password', __('general.current_password')) !!}
									{!! Form::password('current_password', ['class'=>'form-control current_password', 'required'=>true]) !!}
								</div>

								<div class="form-group">
									{!! Form::label('new_password', __('general.new_password')) !!}
									{!! Form::password('new_password', ['class'=>'form-control new_password']) !!}
								</div>

								<div class="form-group">
									{!! Form::label('new_password_confirmation', __('general.confirm_password')) !!}
									{!! Form::password('new_password_confirmation', ['class'=>'form-control new_password_confirmation']) !!}
								</div>

								{!! Form::submit(__('general.update'), ['class'=>'btn btn-primary mb0'] ) !!}

									@if($errors->any())

		                                <div class="alert alert-danger mt20">
		                                    @foreach($errors->all() as $e)
		                                        <li>{{ $e }}</li>
		                                    @endforeach
		                                </div>
									@endif

									@if(!empty($success))
										<div class="alert alert-success mt20">User successfully updated</div>
									@endif

								{!! Form::close() !!}

						</div>

						<div class="col-sm-6 settings">
							<a class="link" href="{{url('dashboards/automatedreports')}}"><i class="fas fa-external-link-alt"></i> {{__('general.auto_report_settings')}}</a>
							<a class="link" href="{{url('dashboards/kpi')}}"><i class="fas fa-external-link-alt"></i> {{__('general.kpi_settings')}}</a>
							<a class="link" href="{{url('dashboards/kpi/recipients')}}"><i class="fas fa-external-link-alt"></i> {{__('general.recipient_settings')}}</a>

							<div class="divider"></div>

							<form action="{{url('/dashboards/settings/update_lang_display')}}" method="POST" class="form hide_lang">
								@csrf
								<label class="checkbox-inline"><input type="checkbox" value="{{Auth::user()->language_displayed ? '1' : '0'}}" name="lang_displayed[]" {{Auth::user()->language_displayed ? 'checked' : ''}}>{{__('general.display_language')}}</label>
								<input type="submit" class="btn btn-primary btn-sm mt30 update_lang_btn" value="{{__('general.update')}}">
							</form>
						</div>
					</div>
			    </div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')

@endsection