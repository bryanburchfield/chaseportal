@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')
	<a href="{{ action('ReportController@info', ['report' => $report]) }}" class="btn btn-primary btn-sm flt_rgt"><i class="fas fa-info-circle"></i> Info</a>
	<h3 class="heading">E-Cover Me Lead Export</h3>

	<div class="report_filters card col-sm-12 fc_style py-4 px-3">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form fc_style']) !!}

			<div class="row">

				@include('shared.report_db_menu')

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('fromdate', 'From Date of Lead') !!}
						<div class="input-group date">
							{!! Form::text('fromdate', $params['fromdate'], ['class'=>'form-control datetimepicker fromdate', 'required' => true, 'autocomplete'=> 'off']) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('todate', 'To Date of Lead') !!}
						<div class="input-group date">
							{!! Form::text('todate', $params['todate'], ['class'=>'form-control datetimepicker todate', 'required' => true, 'autocomplete'=> 'off']) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('call_statuses', __('reports.call_statuses')) !!}
						{!! Form::select("call_statuses[]", $filters['call_statuses'], null, ["class" => "form-control multiselect", 'id'=> 'call_status_select','multiple'=>true]) !!}
					</div>
				</div>

			</div>
			
			<div class="alert alert-danger report_errors"></div>

			{!! Form::hidden('report', $report, ['id'=>'report']) !!}
			<div class="d-flex justify-content-center">
				{!! Form::submit(__('reports.run_report'), ['class'=>'btn btn-primary btn-lg mb-0 mt-5 ']) !!}
			</div>

		{!! Form::close() !!}
	</div><!-- end report_filters -->

	@include('reports.report_tools_inc')

	<div class="table-responsive report_table {{$report}}">
		@include('shared.reporttable')
	</div>

	@include('reports.report_warning_inc')
@endsection