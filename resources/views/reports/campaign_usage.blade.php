@extends('layouts.report')
@section('title', 'Report')

@section('content')
	<h3 class="heading">{{__('reports.campaign_usage')}}</h3>

	<div class="report_filters card col-sm-12">
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
<div class="col-sm-7 nopadright">
	<div class="col-sm-12 nopad">
		<div class="card card-6 hidetilloaded" >
			<h1 class="title fw600">Count of Leads by Attempt</h1>
			<div class="inbound inandout" style="min-height:300px;">
				<canvas id="leads_by_attempt"></canvas>
			</div>
		</div>
	</div>

	<div class="col-sm-6 nopadleft">
		<div class="card hidetilloaded">
			<canvas id="call_stats"></canvas>
		</div>
	</div>

	<div class="col-sm-6 nopadright">
		<div class="card hidetilloaded">
			<canvas id="subcampaigns"></canvas>
		</div>
	</div>
</div>
@endsection