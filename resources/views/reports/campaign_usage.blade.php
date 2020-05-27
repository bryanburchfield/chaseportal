@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')
	<a href="{{ action('ReportController@info', ['report' => $report]) }}" class="btn btn-primary btn-sm flt_rgt"><i class="fas fa-info-circle"></i> Info</a>
	<h3 class="heading">{{__('reports.campaign_usage')}}</h3>

	<div class="report_filters card col-sm-12 fc_style">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form']) !!}

			<div class="row">

				@include('shared.report_db_menu')

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('campaign', __('reports.campaign')) !!}
						{!! Form::select("campaign", $filters['campaign'], null, ["class" => "form-control", 'id'=> 'campaign_select']) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('subcampaign', __('reports.subcampaign')) !!}
						{!! Form::select("subcampaign", $filters['subcampaign'], null, ["class" => "form-control", 'id'=> 'subcampaign_select']) !!}
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
			<h1 class="title fw600">{{__('reports.count_of_leads_by_attempt')}}</h1>
			<div class="inbound inandout cb" style="min-height:300px;">
				<canvas id="leads_by_attempt"></canvas>
			</div>
		</div>
	</div>

	<div class="col-sm-6 nopadleft nopadright pl0">
		<div class="card hidetilloaded">
			<canvas id="call_stats"></canvas>
		</div>
	</div>

	<div class="col-sm-6 nopadleft nopadright pr0">
		<div class="card hidetilloaded">
			<canvas id="subcampaigns"></canvas>
		</div>
	</div>
</div>
@endsection