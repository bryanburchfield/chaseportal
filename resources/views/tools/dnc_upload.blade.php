@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">
			    	<div class="col-sm-12">
						@include('tools.shared.topnav', ['toolpage' => 'dnc'])

						<div class="tab-pane mt30" id="dnc_importer">
                            <h2 class="bbnone mb20">{{__('tools.upload_dnc_file')}}</h2>
                            <ul class="pl10 paditem5">
								<li>{{__('tools.dnc_upload1')}}</li>
								<li>{{__('tools.dnc_upload2')}}</li>
								<li>{{__('tools.dnc_upload3')}}</li>
							</ul>

							<div class="col-sm-4 p0">
								<form enctype="multipart/form-data" method="post">
									@csrf

									<label class="btn btn-info btn-file mb20">
									    {{__('tools.upload_a_file')}} <input type="file" name="dncfile" accept=".csv,.xls,.xlsx,.ods,.slk" style="display: none;">
									</label>

									<p class="filename mb20">{{__('tools.no_file')}}</p>

									<div class="cb mb20 mt30">
										<h4 class="mb10 green-text fw600">{{__('tools.action')}}</h4>
										<label class="radio-inline"><input type="radio" name="action" value="add" {{ old('action') == 'add' ? 'checked' : '' }}>{{ __('tools.add') }}</label>
										<label class="radio-inline"><input type="radio" name="action" value="remove" {{ old('action') == 'remove' ? 'checked' : '' }}>{{ __('tools.remove') }}</label>
									</div>
									
									<div class="checkbox cb mb20">
										<label><input type="checkbox" name="has_headers" {{ old('has_headers') == 'on' ? 'checked' : '' }}><b>{{__('tools.has_header')}}</b></label>
									</div>

									<div class="form-group upload_desc">
										<label>{{__('tools.description')}}</label>
										<input name="description" type="text" class="form-control" />
									</div>

									<input class="btn btn-default btn-cancel mr10" type="submit" name="cancel" value="{{__('general.cancel')}}" />
									<input class="btn btn-primary mb0" type="submit" value="{{__('general.submit')}}" />
								</form>

								@if($errors->any())
									<div class="alert alert-danger mt20">
										@foreach ($errors->all() as $error)
											<p>{{ $error }}</p>
										@endforeach
									</div>
								@endif
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