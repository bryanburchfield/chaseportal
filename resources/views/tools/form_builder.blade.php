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
					<div id="webhook_generator" class="col-sm-12">
						<div class="row">
							<div class="col-sm-4 mt-3 mb20 fc_style">
								<div class="card">
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

							<div class="col-sm-8 mt-3">
								<ul class="nav nav-tabs">
									<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#html">HTML</a></li>
									<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#css">CSS</a></li>
								</ul>

								<div class="tab-content">
									<div id="html" class="tab-pane fade show active">
										<div class="card form_code_preview  mb20">
											<div class="form_code"></div>
										</div>
									</div>

									<div id="css" class="tab-pane fade">
									    <div class="card form_code_preview_css mb20">
									    	<div class="form_code_css">
							<pre class="hljs css">

form.form{
	margin-top:25px;
}

.form-group {
	font-size: 14px;
}

.card{
	background: #eee;
	border: 1px solid #ddd;
	overflow:auto;
}

h2.heading{
	background: #203047;
	color:#fff;
	font-weight: 600;
    font-size: 22px;
    padding: 5px 8px;
    margin-top:0;
	margin-bottom:30px;
}

h4.subheading {
    font-weight: 600;
    font-size: 17px;
    text-transform: uppercase;
    margin-top: 20px;
    margin-bottom: 20px;
    border-bottom: 1px solid #ccc;
}

.card input.form-control, .card select.form-control{
	background:#fff;
	height:40px;
}

.btn.btn-primary{
	margin: 20px auto;
    display: block;
    background:#E15B23;
    border:none;
}

@font-face {
	font-family: myFont;
	src: url('http://www.chasedatacorp.com/assets/fonts/segoeui.ttf');
}

input,
	input::-webkit-input-placeholder {
	font-size: 10px;
}

select:required:invalid {
	color: #999;
	padding-bottom: 0px!important;
	font-size: 12px!important;
}

.hidden {
	display: none;
}

					    	</pre>
					    </div>
				    </div>
				</div>
			</div>
			<button class="generate_code btn btn-primary btn-lg flt_lft mr20">Generate Code</button>
			<button class="download_file btn btn-info btn-lg fw600">Download File</button>
		</div>
	</div>

						<div class="row">
							<div class="col-sm-12 webhook_fields">
								<div class="card">
									<div class="row">
										<div class="col-sm-4">
											<h3 class="mb30">Field Labels</h3>
										</div>

										<div class="col-sm-4">
											<h3 class="mb30">Field Names</h3>
										</div>

										<div class="col-sm-4">
											<h3 class="mb30">Field Values</h3>
										</div>
									</div>

									<div class="all-slides">
										@foreach($default_lead_fields as $val)
											<div class="field slide default row">
												<div class="col-sm-1">
													<a href="#" class="remove_field"><i class="fas fa-times-circle"></i></a>
												</div>
												<div class="col-sm-3">
													<p class="field_label_fb" data-field="{{$val}}">{{$val}}</p>
												</div>

												<div class="col-sm-4">
													<p class="field_name_fb" data-field="{{$val}}">{{$val}}</p>
												</div>

												<div class="col-sm-4">
													<p class="field_value_fb" data-field=""></p>
												</div>
											</div>
										@endforeach
									</div>

									<div class="cloned-slides" id="cloned-slides"></div>
								</div>

								<div class="col-sm-12 p0">
									<form action="#" method="post" class="form add_custom_form_field fc_style card">
										<h3 class="mb20">Add Custom Field</h3>
										<div class="form-group col-sm-4 pl0">
											<input type="text" class="form-control custom_field_label_fb" name="custom_field_label" placeholder="Field Label" required>
										</div>
										<div class="form-group col-sm-4 pl0">
											<input type="text" class="form-control custom_field_name_fb" name="custom_field_name" placeholder="Field Name" required>
										</div>
										<div class="form-group col-sm-4 pl0 pr0">
											<input type="text" class="form-control custom_field_value_fb" name="custom_field_value" placeholder="Field Value">
										</div>
										<input type="submit" class="btn btn-primary btn-lg mt10 mb0" value="Add Custom Field">
									</form>
								</div>

<div class="hidetilloaded html_options">

<div class="head">
<textarea name="head" id="head" cols="30" rows="10">
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://www.chasedatacorp.com/assets/css/font-awesome.min.css" rel="stylesheet">
	<link id="main-sheet" rel="stylesheet" href="https://www.chasedatacorp.com/assets/css/bootstrap.min.css">
	<style>

		form.form{
			margin-top:25px;
		}

		.form-group {
			font-size: 14px;
		}

		.card{
			background: #eee;
			border: 1px solid #ddd;
			overflow:auto;
		}

		h2.heading{
			background: #203047;
			color:#fff;
			font-weight: 600;
		    font-size: 22px;
		    padding: 5px 8px;
		    margin-top: 0;
			margin-bottom:30px;
		}

		h4.subheading {
		    font-weight: 600;
		    font-size: 17px;
		    text-transform: uppercase;
		    margin-top: 20px;
		    margin-bottom: 20px;
		    border-bottom: 1px solid #ccc;
		}

		.card input.form-control, .card select.form-control{
			background:#fff;
			height:40px;
		}

		.btn.btn-primary{
			margin: 20px auto;
		    display: block;
		    background:#E15B23;
		    border:none;
		}

		@font-face {
			font-family: myFont;
			src: url('http://www.chasedatacorp.com/assets/fonts/segoeui.ttf');
		}

		input,
			input::-webkit-input-placeholder {
			font-size: 10px;
		}

		select:required:invalid {
			color: #999;
			padding-bottom: 0px!important;
			font-size: 12px!important;
		}

		.hidden {
			display: none;
		}
	</style>
</head>
<body>

<div class="container">
	<div class="row">
		{{-- <div class="col-sm-12"> --}}
		{{-- <h2 class="heading">Contact Information</h2> --}}
		{{-- </div> --}}

		<form control="form" class="form card fc_style">
			<h2 class="heading">Contact Information</h2>
			<div class="col-sm-12"><h4 class="subheading">Contact Person</h4></div>
</textarea>
</div>

	<div class="input">
		<div class="col-sm-6">
			<div class="form-group">
				<label>Label</label>
				<input type="text" class="form-control control-input" name="label" control="input" field-name="label" id="label">
			</div>
		</div>
	</div>

	<div class="input-4">
		<div class="col-sm-4">
			<div class="form-group">
				<label>Label</label>
				<input type="text" class="form-control control-input" name="label" control="input" field-name="label" id="label">
			</div>
		</div>
	</div>

	<div class="textarea-12">
		<div class="col-sm-12">
			<div class="form-group">
				<label>Notes</label>
				<textarea name="label" id="label" field-name="label" cols="30" rows="10" class="form-control control-input" control="textarea"></textarea>
			</div>
		</div>
	</div>

	<div class="input-12">
		<div class="col-sm-12">
			<div class="form-group">
				<label>Label</label>
				<input type="text" class="form-control control-input" name="label" control="input" field-name="label" id="label">
			</div>
		</div>
	</div>

	<div class="select_state">
		<div class="col-sm-4">
			<div class="form-group">
				<label>State</label>
				<select name="state" id="state" class="form-control control-select">
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

	<div class="bottom">
	<textarea name="bottom" id="bottom" cols="30" rows="10" class="form-control control-input" control="textarea">
		</form>
	</div>
</div>
</body>
</html>
</textarea>

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