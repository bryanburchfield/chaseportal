@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')
	<a href="{{ action('ReportController@info', ['report' => $report]) }}" class="btn btn-primary btn-sm flt_rgt"><i class="fas fa-info-circle"></i> Info</a>
	<h3 class="heading">{{__('reports.group_duration')}}</h3>

	<div class="report_filters card col-sm-12 fc_style">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form']) !!}

			<div class="row">
				@include('shared.report_db_menu')
				
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('fromdate', __('reports.from')) !!}
						<div class="input-group date">
							{!! Form::text('fromdate', $params['fromdate'], ['class'=>'form-control datetimepicker', 'required' => true, 'autocomplete'=> 'off']) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('todate', __('reports.to')) !!}
						<div class="input-group date">
							{!! Form::text('todate', $params['todate'], ['class'=>'form-control datetimepicker', 'required' => true, 'autocomplete'=> 'off']) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				@can('accessSuperAdmin')
					<div class="col-sm-4">
						<div class="form-group">
							{!! Form::label('dialer', __('reports.dialer')) !!}
							{!! Form::select("dialer", [null=>__('general.select_one')] + $filters['dialers'], null, ["class" => "form-control", 'id'=> 'dialer']) !!}
						</div>
					</div>
					
					<div class="col-sm-4">
						<div class="form-group">
							<div class="dropdown mb20">
								<label>{{__('tools.group_select')}}</label>
								<button class="btn btn-default dropdown-toggle myselect" type="button" id="group_select" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								{{__('tools.group_select')}}
								<span class="caret"></span>
								</button>
								<ul class="dropdown-menu group_select stop-propagation" aria-labelledby="group_select"></ul>
							</div>
						</div>
					</div>
				@endcan

			</div>

			<div class="alert alert-danger report_errors"></div>

			{!! Form::hidden('report', $report, ['id'=>'report']) !!}
			{!! Form::submit(__('reports.run_report'), ['class'=>'btn btn-primary mb0']) !!}

		{!! Form::close() !!}
	</div><!-- end report_filters -->

	@include('reports.report_tools_inc')

	<div class="pinned_table table-responsive report_table {{$report}}">
		@include('shared.reporttable')
	</div>

	@include('reports.report_warning_inc')
@endsection