@extends('layouts.master')
@section('title', 'Report')

@section('content')
<div class="preloader"></div>

<div class="wrapper">

	{{-- @include('shared.sidenav') --}}
	<div id="content">
		<div class="container-fluid bg dashboard p20">
			<h1>Call Details</h1>

			{!! Form::open(['method'=> 'POST', 'url' => '#']) !!}

				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('fromdate', 'From') !!}
							<div class="input-group date">
								{!! Form::text('fromdate', null, ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true]) !!}
								<span class="input-group-addon">
				                    <span class="glyphicon glyphicon-calendar">
				                    </span>
				                </span>
							</div>
						</div>
					</div>

					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('todate', 'To') !!}
							<div class="input-group date">
								{!! Form::text('todate', null, ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true]) !!}
								<span class="input-group-addon">
				                    <span class="glyphicon glyphicon-calendar">
				                    </span>
				                </span>
							</div>
						</div>
					</div>

					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('campaign', 'Campaign') !!}
							{!! Form::select("campaign[]", $campaigns, null, ["class" => "form-control multiselect", 'id'=> 'campaign_select','multiple'=>true]) !!}
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('callerid', 'Inbound Sources') !!}
							{!! Form::select("callerid[]", $inbound_sources, null, ["class" => "form-control multiselect", 'id'=> 'inbound_sources_select','multiple'=>true]) !!}
						</div>
					</div>

					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('rep', 'Rep') !!}
							{!! Form::select("rep[]", $rep, null, ["class" => "form-control multiselect", 'id'=> 'rep_select','multiple'=>true]) !!}
						</div>
					</div>

					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('call_status', 'Call Status') !!}
							{!! Form::select("call_status[]", $call_status, null, ["class" => "form-control multiselect", 'id'=> 'call_status_select','multiple'=>true]) !!}
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('calltype', 'Call Type') !!}
							{!! Form::select("calltype[]", ['0'=>'Outbound', '1'=>'Inbound', '2'=>'Manual', '3'=>'Transferred', '4'=>'Conference', '5'=>'Progressive', '6'=>'Text Message'], null, ["class" => "form-control", 'id'=> 'call_type']) !!}
						</div>
					</div>

					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('durationfrom', 'Duration ( seconds )') !!}
							<div class="input-group">
								{!! Form::text('durationfrom', null, ['class'=>'form-control', 'placeholder'=>'Start']) !!}
							    <span class="input-group-addon">-</span>
							    {!! Form::text('durationto', null, ['class'=>'form-control', 'placeholder'=>'End']) !!}
							</div>
						</div>
					</div>

					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('phone', 'Phone') !!}
							{!! Form::tel('phone', null, ['class'=>'form-control', 'required' => true]) !!}
						</div>
					</div>
				</div>
				
				<div class="checkbox">
					<label>Show only termination status
						{!! Form::checkbox('showonlyterm',false, ['id'=>'showonlyterm']) !!}
					</label>
				</div>

				<div class="alert alert-danger report_errors"></div>

				{!! Form::hidden('report', 'call_details', ['id'=>'report']) !!}
				{!! Form::submit('Run Report', ['class'=>'btn btn-primary mb0']) !!}

			{!! Form::close() !!}
		</div>
	</div>
</div>
@endsection