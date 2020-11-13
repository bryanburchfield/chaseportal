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

								<div class="form-group">
									<h4 class="mb10">Submit Button Type</h4>
									<label class="radio-inline"><input class="submit_btn_type" value="submit" type="radio" name="submit_btn_type" checked>Submit</label>
									<label class="radio-inline"><input class="submit_btn_type" value="submit_navigate" type="radio" name="submit_btn_type">Submit and Navigate</label>
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

									<div class="all-slides">
										@foreach($default_lead_fields as $val)
											<div class="field slide">
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

									<div class="cloned-slides" id="cloned-slides"></div>
								</div>

								<div class="col-sm-5 p0">
									<form action="#" method="post" class="form add_custom_form_field fc_style card">
										<h3 class="mb20">Add Custom Field</h3>
										<div class="form-group col-sm-6 pl0">
											<input type="text" class="form-control custom_field_name" name="custom_field_name" placeholder="Field Name" required>
										</div>
										<div class="form-group col-sm-6 pl0 pr0">
											<input type="text" class="form-control custom_field_value" name="custom_field_value" placeholder="Field Value" required>
										</div>
										<input type="submit" class="btn btn-primary mt30 h35" value="Add Custom Field">
									</form>
								</div>

<div class="hidetilloaded html_options">
<div class="input">
	<div class="form-group">
		<label>Label</label>
		<input type="text" class="form-control" name="label" field-name="label" id="label">
	</div>
</div>

<div class="select_state">
	<div class="form-group">
		<select name="state" id="state">
			<option value="" selected="selected">Select a State</option>
			<option value="AL">Alabama</option>
			<option value="AK">Alaska</option>
			<option value="AZ">Arizona</option>
			<option value="AR">Arkansas</option>
			<option value="CA">California</option>
			<option value="CO">Colorado</option>
			<option value="CT">Connecticut</option>
			<option value="DE">Delaware</option>
			<option value="DC">District Of Columbia</option>
			<option value="FL">Florida</option>
			<option value="GA">Georgia</option>
			<option value="HI">Hawaii</option>
			<option value="ID">Idaho</option>
			<option value="IL">Illinois</option>
			<option value="IN">Indiana</option>
			<option value="IA">Iowa</option>
			<option value="KS">Kansas</option>
			<option value="KY">Kentucky</option>
			<option value="LA">Louisiana</option>
			<option value="ME">Maine</option>
			<option value="MD">Maryland</option>
			<option value="MA">Massachusetts</option>
			<option value="MI">Michigan</option>
			<option value="MN">Minnesota</option>
			<option value="MS">Mississippi</option>
			<option value="MO">Missouri</option>
			<option value="MT">Montana</option>
			<option value="NE">Nebraska</option>
			<option value="NV">Nevada</option>
			<option value="NH">New Hampshire</option>
			<option value="NJ">New Jersey</option>
			<option value="NM">New Mexico</option>
			<option value="NY">New York</option>
			<option value="NC">North Carolina</option>
			<option value="ND">North Dakota</option>
			<option value="OH">Ohio</option>
			<option value="OK">Oklahoma</option>
			<option value="OR">Oregon</option>
			<option value="PA">Pennsylvania</option>
			<option value="RI">Rhode Island</option>
			<option value="SC">South Carolina</option>
			<option value="SD">South Dakota</option>
			<option value="TN">Tennessee</option>
			<option value="TX">Texas</option>
			<option value="UT">Utah</option>
			<option value="VT">Vermont</option>
			<option value="VA">Virginia</option>
			<option value="WA">Washington</option>
			<option value="WV">West Virginia</option>
			<option value="WI">Wisconsin</option>
			<option value="WY">Wyoming</option>
		</select>
	</div>
</div>
</div>

								<div class="col-sm-7">
									{{-- <div class="final_url_cnt">
										<textarea data-toggle="tooltip"  title="Link Copied!" cols="30" rows="7" class="url form-control"></textarea>
									</div> --}}

									<div class="card form_code_preview hidetilloaded mb20">
										<div class="form_code" data-toggle="tooltip" data-placement="bottom"  title="Code Copied!"></div>
									</div>
									<button class="generate_code btn btn-primary btn-lg">Generate Code</button>
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