@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')
	<a href="{{ action('ReportController@info', ['report' => $report]) }}" class="btn btn-primary btn-sm flt_rgt"><i class="fas fa-info-circle"></i> Info</a>
	<h3 class="heading">{{__('reports.campaign_call_log')}}</h3>

	<div class="report_filters card col-sm-12 fc_style">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form query_dates_first']) !!}

			<div class="row display-flex">

				@include('shared.report_db_menu')
				
				<div class="col-sm-4 mb-2">
					<div class="form-group">
						{!! Form::label('fromdate', __('reports.from')) !!}
						<div class="input-group date">
							{!! Form::text('fromdate', $params['fromdate'], ['class'=>'form-control datetimepicker fromdate', 'required' => true, 'autocomplete'=> 'off']) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4 mb-2">
					<div class="form-group">
						{!! Form::label('todate', __('reports.to')) !!}
						<div class="input-group date">
							{!! Form::text('todate', $params['todate'], ['class'=>'form-control datetimepicker todate', 'required' => true, 'autocomplete'=> 'off']) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4 mb-2">
					<div class="form-group">
						{!! Form::label('campaigns', __('reports.campaign')) !!}
						{!! Form::select("campaigns[]", $filters['campaigns'], null, ["class" => "form-control multiselect", 'id'=> 'campaign_select','multiple'=>true]) !!}
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-4 mb-2">
					<div class="form-group">
						{!! Form::label('reps', __('reports.rep')) !!}
						<select class="form-control multiselect" id="rep_select" multiple name="reps[]">
							@foreach($filters['reps'] as $rep)
								<option class="{{ $rep['IsActive'] ? 'active_rep' : ''}}" value="{{$rep['RepName']}}" data-active="{{$rep['IsActive']}}">{{$rep['RepName']}}</option>
							@endforeach
						</select>
						<label class="checkbox toggle_active_reps pl-0 mt-4"><input type="checkbox"> {{__('reports.show_active_reps')}}</label>
					</div>
				</div>
			</div>

			<div class="alert alert-danger report_errors"></div>


			{!! Form::hidden('report', $report, ['id'=>'report']) !!}
			{!! Form::submit(__('reports.run_report'), ['class'=>'btn btn-primary mb0']) !!}

		{!! Form::close() !!}
	</div><!-- end report_filters -->

	@include('reports.report_tools_inc')

	<div class="table-responsive report_table {{$report}}">
		@include('shared.reporttable')
	</div>

	@include('reports.report_warning_inc')
@endsection

@section('extras')
<div class="col-sm-7 col-xs-12 nopadright pl0">
	<div class="col-sm-12 p0">
		<div class="card card-6 hidetilloaded" >
			<h1 class="title fw600">{{__('reports.call_vol_per_int')}}</h1>
			<div class="inbound inandout cb" style="min-height:300px;">
				<canvas id="call_volume"></canvas>
			</div>
		</div>
	</div>
	
	<div class="col-sm-6 nopadleft nopadright pl0">
		<div class="card hidetilloaded">
			<!-- count of callstatuses -->
			<canvas id="callstatus"></canvas>
		</div>
	</div>

	<div class="col-sm-6 nopadleft nopadright pr0">
		<div class="card hidetilloaded">
			<!-- agent calls vs system calls -->
			<canvas id="agent_system_calls"></canvas>
		</div>
	</div>
</div>	
@endsection