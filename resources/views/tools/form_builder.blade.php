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
							<div class="col-sm-4 mt30 mb20 card fc_style">
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
											<div class="field slide default">
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

								<div class="col-sm-5 p0">
									<form action="#" method="post" class="form add_custom_form_field fc_style card">
										<h3 class="mb20">Add Custom Field</h3>
										<div class="form-group col-sm-6 pl0">
											<input type="text" class="form-control custom_field_name_fb" name="custom_field_name" placeholder="Field Name" required>
										</div>
										<div class="form-group col-sm-6 pl0 pr0">
											<input type="text" class="form-control custom_field_value_fb" name="custom_field_value" placeholder="Field Value" required>
										</div>
										<input type="submit" class="btn btn-primary mt30 h35" value="Add Custom Field">
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
</head>
<body>

<div class="container">
	<div class="row">
		<form action="#" method="post" class="form">
</textarea>

</div>

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

	<div class="bottom">
	<textarea name="bottom" id="bottom" cols="30" rows="10">
		</form>
	</div>
</div>
</body>
</html>
</textarea>

</div>
</div>

								<div class="col-sm-7 pr0">
									<ul class="nav nav-tabs">
										<li class="active"><a data-toggle="tab" href="#html">HTML</a></li>
										<li><a data-toggle="tab" href="#css">CSS</a></li>
									</ul>

									<div class="tab-content">
										<div id="html" class="tab-pane fade in active">
											<div class="card form_code_preview  mb20">
												<div class="form_code" data-toggle="tooltip" data-placement="bottom"  title="Code Copied!"></div>
											</div>
										</div>

										<div id="css" class="tab-pane fade">
										    <div class="card form_code_preview_css mb20">
										    	<div class="form_code_css" data-toggle="tooltip" data-placement="bottom"  title="Code Copied!">
<pre class="hljs css">

.dynamic-form > .panel-primary > .panel-body, .dynamic-form > .panel-primary > .panel-body > .form-group {
	font-size: 14px;
}

.dynamic-form > .panel-primary > .panel-body > .form-group > h4 {
	margin: 10px 0px 10px 0px;
}

.dynamic-form > .panel-primary > .panel-body > .form-group > .locked {
	margin: 10px 0px;
}

.dynamic-form > .panel-primary > .panel-body > .form-group > h4 {
	margin: 10px 0px 10px 0px;
}

.dynamic-form > .panel-primary > .panel-body > .form-group > .locked {
	margin: 10px 0px;
}

.dynamic-form > .panel-primary > .panel-body > .form-group > h4 {
	margin: 10px 0px 10px 0px;
}

.dynamic-form > .panel-primary > .panel-body > .form-group > .locked {
	margin: 10px 0px;
}

.dynamic-form > .panel-primary > .panel-body > .form-group > h4 {
	margin: 10px 0px 10px 0px;
}
.panel-heading{
	background:#2d3c5a!important;
}
.dynamic-form > .panel-primary > .panel-body > .form-group > h4 {
	font-weight: 700;
}

.dynamic-form > .panel-primary > .panel-body > .form-group > h4 {
	font-weight: 700;
}

.dynamic-form > .panel-primary > .panel-body > .form-group > h4 {
	font-weight: 600;
}

.dynamic-form > .panel-primary > .panel-heading > h3 {
	margin: 0px 0px 0px 0px;
}

@font-face {
	font-family: myFont;
	src: url('http://www.chasedatacorp.com/assets/fonts/segoeui.ttf');
}

body{
	font-family:myFont;
}

.fname{
	display:inline-block;
	width:49.5%;
	float:left;
	margin-bottom :15px;
}

.lname{
	display:inline-block;
	width:49.5%;
	float:right;
	margin-bottom :15px;
}

.panel-body{
	background-color:#eee;
}

input[type=submit]{
	background: #2d3c5a;
	color: white;
	height: 40px;
	font-size: 20px;
	padding: 0px 0px;
	letter-spacing: 2px;
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

select{
	padding-right: calc(24px);
	background-size: .5em;
	background-image: url("data:image/svg+xml;utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%201000%201805.18%22%3E%3Cpath%20fill%3D%22%23000000%22%20d%3D%22M461.6%2C643.4L10.9%2C79.9C-14.9%2C47.7%2C8%2C0%2C49.3%2C0h901.5c41.2%2C0%2C64.1%2C47.7%2C38.4%2C79.9L538.4%2C643.4%09C518.7%2C668%2C481.3%2C668%2C461.6%2C643.4z%22%20transform%3D%22rotate%28180%20500%20902.59%29%20translate%280%201143.28%29%22%3E%3C%2Fpath%3E%3Cpath%20fill%3D%22%23000000%22%20d%3D%22M461.6%2C643.4L10.9%2C79.9C-14.9%2C47.7%2C8%2C0%2C49.3%2C0h901.5c41.2%2C0%2C64.1%2C47.7%2C38.4%2C79.9L538.4%2C643.4%09C518.7%2C668%2C481.3%2C668%2C461.6%2C643.4z%22%20transform%3D%22translate%280%201143.28%29%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E")!important;
	background-repeat: no-repeat;
	background-position: right 6px top 50%;
	-webkit-appearance: none;
}
option[value=""]{
display: none;
}

h4{
letter-spacing:2px;
}

.first{
display:inline-block!important;
width:33%!important;
margin-right:0.5%;
float:left;
padding-bottom: 15px;
}
.second{
display:inline;
width:33%!important;
float:left;
margin-right:0.5%;
padding-bottom: 15px;
}

.third{
display:inline;
width:33%!important;
float:right;
padding-bottom: 15px;
}


.padding{
padding-top:40px;
}
.hidden {
display: none;   
}
}

table.MsoNormalTable
{
line-height:107%;
font-size:11.0pt;
font-family:"Calibri",sans-serif;
}
table.MsoNormalTable
{
line-height:107%;
font-size:11.0pt;
font-family:"Calibri",sans-serif;
}
										    		</pre>
										    	</div>
										    </div>
										</div>
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