@extends('layouts.master')
@section('title', __('widgets.admin'))

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
						<div id="webhook_generator">
							<div class="col-sm-6 mt30 mb20 card fc_style">
								<h2 class="page_heading">Form Builder</h2>
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
									<select name="client_table" id="client_table_menu" class="form-control"></select>
								</div>

								<div class="alert alert-danger hidetilloaded"></div>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-12 webhook_fields">
								<div class="card">
									<div class="row">
										<div class="col-sm-5">
											<h3 class="mb30">Field Labels</h3>
										</div>

										<div class="col-sm-5">
											<h3 class="mb30">Field Names</h3>
										</div>

										<div class="col-sm-2">
											{{-- <label class="checkbox-inline flt_rgt"><input class="checkall_inputs" type="checkbox" value=""><span>Select All Inputs</span></label> --}}
										</div>
									</div>

									@foreach($default_lead_fields as $val)
										<div class="field">
											<div class="col-sm-1">
												<a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a>
											</div>
											<div class="col-sm-4">
												<p class="field_label" data-field="{{$val}}">{{$val}}</p>
											</div>

											<div class="col-sm-5">
												<div class="form-group">
													<input type="text" class="form-control field_name" name="{{$val}}" placeholder="{{$val}}" value="{{$val}}">
												</div>
											</div>

											<div class="col-sm-2">
												{{-- <div class="form-group">
													<select name="field_type" class="form-control field_type">
														<option value="">Select One</option>
														<option value="input">Input</option>
														<option value="textarea">Textarea</option>
													</select>
												</div> --}}
											</div>
										</div>
									@endforeach
								</div>

								<div class="col-sm-5 p0">
									<form action="#" method="post" class="form-inline add_custom_form_field fc_style card">
										<h3 class="mb20">Add Custom Field</h3>
										<div class="form-group mr10">
											<input type="text" class="form-control custom_field_name" name="custom_field_name" placeholder="Field Name" required>
										</div>
										<div class="form-group mr10">
											<input type="text" class="form-control custom_field_value" name="custom_field_value" placeholder="Field Value" required>
										</div>
										<input type="submit" class="btn btn-primary mt30 h35" value="Add Custom Field">
									</form>
								</div>

<div class="hidetilloaded html_options">
<div class="form-group">
	<label>Label</label>
	<input type="text" class="form-control" name="label" field-name="label" id="label">
</div>
</div>

								<div class="col-sm-7">
									{{-- <div class="final_url_cnt">
										<textarea data-toggle="tooltip"  title="Link Copied!" cols="30" rows="7" class="url form-control"></textarea>
									</div> --}}

									<div class="card form_code_preview hidetilloaded">
										<div class="form_code" data-toggle="tooltip" data-placement="bottom"  title="Code Copied!"></div>
									</div>
									<button class="generate_code btn btn-primary btn-lg mt20">Generate Code</button>
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