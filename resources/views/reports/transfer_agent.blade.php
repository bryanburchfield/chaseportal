@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')
	<a href="{{ action('ReportController@info', ['report' => $report]) }}" class="btn btn-primary btn-sm flt_rgt"><i class="fas fa-info-circle"></i> Info</a>
	<h3 class="heading">{{__('reports.transfer_agent')}}</h3>

	<div class="report_filters card col-sm-12 fc_style">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form']) !!}

			<div class="row">

				@include('shared.report_db_menu')

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('fromdate', __('reports.from')) !!}
						<div class="input-group date">
							{!! Form::text('fromdate', $date = isset($_POST['fromdate']) ? $_POST['fromdate'] : $params['fromdate'], ['class'=>'form-control datetimepicker', 'required' => true, 'autocomplete'=> 'off']) !!}
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
							{!! Form::text('todate', $date = isset($_POST['todate']) ? $_POST['todate'] : $params['todate'], ['class'=>'form-control datetimepicker', 'required' => true, 'autocomplete'=> 'off']) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('reps',  __('reports.rep')) !!}
						<select class="form-control multiselect" id="rep_select" multiple name="reps[]">
							@foreach($filters['reps'] as $rep)
								<option class="{{ $rep['IsActive'] ? 'active_rep' : ''}}" value="{{$rep['RepName']}}" data-active="{{$rep['IsActive']}}">{{$rep['RepName']}}</option>
							@endforeach
						</select>
						<label class="checkbox toggle_active_reps"><input type="checkbox"> {{__('reports.show_active_reps')}}</label>
					</div>
                </div>

                <div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('closers',  __('reports.closer')) !!}
						<select class="form-control multiselect" id="closer_select" multiple name="closers[]">
							@foreach($filters['closers'] as $closer)
								<option class="{{ $closer['IsActive'] ? 'active_rep' : ''}}" value="{{$closer['RepName']}}" data-active="{{$closer['IsActive']}}">{{$closer['RepName']}}</option>
							@endforeach
						</select>
						<label class="checkbox toggle_active_reps"><input type="checkbox"> {{__('reports.show_active_reps')}}</label>
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