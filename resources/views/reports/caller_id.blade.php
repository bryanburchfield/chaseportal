@extends('layouts.report')
@section('title', 'Report')

@section('content')
	<h3 class="heading">Caller ID</h3>

	<div class="report_filters card col-sm-12">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form']) !!}

			<div class="row">

				@include('shared.report_db_menu')

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('fromdate', 'From') !!}
						<div class="input-group date">
							{!! Form::text('fromdate', $params['fromdate'], ['class'=>'form-control datetimepicker', 'required' => true]) !!}
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
							{!! Form::text('todate', $params['todate'], ['class'=>'form-control datetimepicker', 'required' => true]) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('caller_id', 'Caller ID') !!}
						{!! Form::tel('caller_id', null, ['class'=>'form-control', 'required' => false]) !!}
					</div>
				</div>
			</div>

			<div class="alert alert-danger report_errors"></div>


			{!! Form::hidden('report', $report, ['id'=>'report']) !!}
			{!! Form::submit('Run Report', ['class'=>'btn btn-primary mb0']) !!}

		{!! Form::close() !!}
	</div><!-- end report_filters -->

	<div class="row">
		<div class="col-sm-12">
			<div class="card card-6 hidetilloaded pb5" >
				<h1 class="title fw600">Calls by Caller ID</h1>
				<div class="inbound inandout" style="min-height:300px;">
					<canvas id="caller_id_graph"></canvas>
				</div>
			</div>
		</div>
	</div>
	@include('reports.report_tools_inc')

	<div class="table-responsive report_table {{$report}}">
		@include('shared.reporttable')
	</div>

	@include('reports.report_warning_inc')
@endsection