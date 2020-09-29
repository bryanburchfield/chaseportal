@extends('layouts.master')
@section('title', __('widgets.admin'))

@section('content')
<div class="preloader"></div>
<div class="wrapper">

	@include('shared.admin_sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt-5">
				<div class="row">
					<div class="col-sm-12">
						<div id="webhook_generator">
							<div class="col-sm-6 mt30 mb20 card fc_style">
								<h2 class="page_heading">Webhook Generator</h2>
								<div class="form-group">
									<label>Group ID</label>
									<input type="text" class="form-control" name="group_id" id="group_id">
								</div>

								<div class="form-group">
					                {!! Form::label('db', 'Database') !!}
					                {!! Form::select("db", $dbs, null, ["class" => "form-control", 'id'=> 'db', 'required'=>true]) !!}
					            </div>

								<div class="form-group">
									<label>Table</label>
									<select name="client_table" id="client_table" class="form-control"></select>
								</div>

								<div class="form-group">
									<label>Posting URL</label>
									<input type="text" class="form-control" name="posting_url" id="posting_url">
								</div>

								<div class="alert alert-danger hidetilloaded"></div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-12 webhook_fields">
								<div class="card">
									<div class="row">
										<div class="col-sm-5">
											<h3 class="mb30">Field Names</h3>
										</div>

										<div class="col-sm-5">
											<h3 class="mb30">Values</h3>
										</div>

										<div class="col-sm-2">
											<label class="checkbox-inline float-right"><input class="checkall_system_macro" type="checkbox" value=""><span> Check All Macros</span></label>
										</div>
									</div>

									@foreach($default_lead_fields as $val)
										<div class="row field">
											<div class="col-sm-1">
												<a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a>
											</div>
											<div class="col-sm-4">
												<p class="field_name" data-field="{{$val}}">{{$val}}</p>
											</div>

											<div class="col-sm-5">
												<div class="form-group">
													<input type="text" class="form-control" name="{{$val}}" placeholder="{{$val}}">
												</div>
											</div>

											<div class="col-sm-2">
												<label class="checkbox-inline float-right"><input class="use_system_macro" type="checkbox" value=""> Use System Macro</label>
											</div>
										</div>
									@endforeach
								</div>

								<div class="row">
									<div class="col-sm-7">
										<div class="card">
											<div class="row">
												<div class="col-sm-12">
													<h2 class="mb-0">Add Custom Field</h2>
												</div>
											</div>

											<form action="#" method="post" class="form-inline add_custom_field fc_style">
												<div class="row mt-4 p-0">
													<div class="col-sm-4">
														<div class="form-group mr-2">
															<input type="text" class="form-control custom_field_name" name="custom_field_name" placeholder="Field Name" required>
														</div>
													</div>
													<div class="col-sm-4">
														<div class="form-group mr-2">
															<input type="text" class="form-control custom_field_value" name="custom_field_value" placeholder="Field Value" required>
														</div>
													</div>
													<div class="col-sm-4">
														<input type="submit" class="btn btn-primary" value="Add Custom Field">
													</div>
												</div>
											</form>
										</div>
									</div>

									<div class="col-sm-5">
										<div class="final_url_cnt">
											<textarea data-toggle="tooltip"  title="Link Copied!" cols="30" rows="7" class="url form-control"></textarea>
										</div>
										<button class="generate_url btn btn-primary btn-lg mt20">Generate URL</button>
									</div>
								</div>

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