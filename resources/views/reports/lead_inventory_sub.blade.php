@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')
	<a href="{{ action('ReportController@info', ['report' => $report]) }}" class="btn btn-primary btn-sm flt_rgt"><i class="fas fa-info-circle"></i> Info</a>
	<h3 class="heading">{{__('reports.lead_inventory_sub')}}</h3>

	<div class="report_filters card col-sm-12 fc_style">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form']) !!}

			<div class="row">
				@include('shared.report_db_menu')

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('fromdate', __('reports.from')) !!}
						<div class="input-group date">
							{!! Form::text('fromdate', $date = isset($_POST['fromdate']) ? $_POST['fromdate'] : $params['fromdate'], ['class'=>'form-control datetimepicker', 'required' => false, 'autocomplete' => 'off', 'placeholder' => __('general.date_range_optional')]) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('todate',  __('reports.to')) !!}
						<div class="input-group date">
							{!! Form::text('todate', $date = isset($_POST['todate']) ? $_POST['todate'] : $params['todate'], ['class'=>'form-control datetimepicker', 'required' => false, 'autocomplete' => 'off', 'placeholder' =>  __('general.date_range_optional')]) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

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

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('attemptsfrom', __('reports.attempts') . ' (' . __('reports.optional') . ')') !!}
						<div class="input-group">
                            {!! Form::text('attemptsfrom', null, ['class'=>'form-control', 'placeholder'=>__('reports.from')]) !!}
						    <span class="input-group-addon">-</span>
						    {!! Form::text('attemptsto', null, ['class'=>'form-control', 'placeholder'=>__('reports.to')]) !!}
						</div>
					</div>
				</div>

                <div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('is_callable', __('reports.is_callable') . ' (' . __('reports.optional') . ')') !!}
						{!! Form::select("is_callable", $filters['is_callable'], null, ["class" => "form-control", 'id'=> 'is_callable']) !!}
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
	<h4 class="total_leads mb15 mt30"></h4>
	<h4 class="available_leads mb15"></h4>

@endsection