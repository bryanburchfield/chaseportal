@extends('layouts.report')
@section('title', 'Report')

@section('content')
	<h3 class="heading">Campaign Call Log</h3>

	<div class="report_filters card col-sm-12">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form']) !!}

			<div class="row">

				@include('shared.report_db_menu')
				
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('fromdate', 'From') !!}
						<div class="input-group date">
							{!! Form::text('fromdate', $date = isset($_POST['fromdate']) ? $_POST['fromdate'] : $params['fromdate'], ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true]) !!}
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
							{!! Form::text('todate', $date = isset($_POST['todate']) ? $_POST['todate'] : $params['todate'], ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true]) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('campaigns', 'Campaign') !!}
						{!! Form::select("campaigns[]", $filters['campaigns'], null, ["class" => "form-control multiselect", 'id'=> 'campaign_select','multiple'=>true]) !!}
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('reps', 'Rep') !!}
						{!! Form::select("reps[]", $filters['reps'], null, ["class" => "form-control multiselect", 'id'=> 'rep_select','multiple'=>true]) !!}
					</div>
				</div>
			</div>

			<div class='reporterrors'>
				@include('shared.reporterrors')
			</div>

			{!! Form::hidden('report', $report, ['id'=>'report']) !!}
			{!! Form::submit('Run Report', ['class'=>'btn btn-primary mb0']) !!}

		{!! Form::close() !!}
	</div><!-- end report_filters -->

	@include('reports.report_tools_inc')

	<div class="table-responsive report_table {{$report}}">
		@include('shared.reporttable')
	</div>

	@include('reports.report_warning_inc')
@endsection

@section('extras')
<div class="col-sm-7 nopadright">
	<div class="col-sm-12 nopad">
		<div class="card card-6 hidetilloaded" >
			<h1 class="title fw600">Call Volume Per 15 Min Interval</h1>
			<div class="inbound inandout" style="min-height:300px;">
				<canvas id="call_volume"></canvas>
			</div>
		</div>
	</div>
	
	<div class="col-sm-6 nopadleft">
		<div class="card hidetilloaded">
			<!-- count of callstatuses -->
			<canvas id="callstatus"></canvas>
		</div>
	</div>

	<div class="col-sm-6 nopadright">
		<div class="card hidetilloaded">
			<!-- agent calls vs system calls -->
			<canvas id="agent_system_calls"></canvas>
		</div>
	</div>
</div>	
@endsection