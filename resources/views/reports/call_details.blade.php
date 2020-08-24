@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')
	<a href="{{ action('ReportController@info', ['report' => $report]) }}" class="btn btn-primary btn-sm flt_rgt"><i class="fas fa-info-circle"></i> Info</a>
	<h3 class="heading">{{__('reports.call_details')}}</h3>

	<div class="report_filters card col-sm-12">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form fc_style']) !!}

			<div class="row">

				@include('shared.report_db_menu')

				<div class="col-sm-4">
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

				<div class="col-sm-4">
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

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('campaigns', __('reports.campaign')) !!}
						{!! Form::select("campaigns[]", $filters['campaigns'], null, ["class" => "form-control multiselect", 'id'=> 'campaign_select','multiple'=>true]) !!}
					</div>
				</div>

				<div class="col-sm-4 hidetilloaded">
					<div class="form-group">
						{!! Form::label('subcampaign', __('reports.subcampaigns')) !!}
						{!! Form::select("subcampaigns", $filters['subcampaign'], null, ["class" => "form-control multiselect", 'id'=> 'subcampaign_select',  'multiple'=>true]) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('callerids', __('reports.inbound_sources')) !!}
						{!! Form::select("callerids[]", $filters['inbound_sources'], null, ["class" => "form-control multiselect", 'id'=> 'inbound_sources_select','multiple'=>true]) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('callerid', __('reports.callerid')) !!}
						{!! Form::tel('callerid', null, ['class'=>'form-control', 'required' => false]) !!}					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('reps', __('reports.rep')) !!}
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
						{!! Form::label('skills', __('reports.skill')) !!}
						{!! Form::select("skills[]", $filters['skills'], null, ["class" => "form-control multiselect", 'id'=> 'skill_select','multiple'=>true]) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('call_statuses', __('reports.call_statuses')) !!}
						{!! Form::select("call_statuses[]", $filters['call_statuses'], null, ["class" => "form-control multiselect", 'id'=> 'call_status_select','multiple'=>true]) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('is_callable', __('reports.is_callable')) !!}
						{!! Form::select("is_callable", $filters['is_callable'], null, ["class" => "form-control", 'id'=> 'is_callable']) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('call_type', __('reports.call_type')) !!}
						{!! Form::select("call_type", $filters['call_types'], null, ["class" => "form-control", 'id'=> 'call_type']) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('durationfrom', __('reports.duration_secs')) !!}
						<div class="input-group">
							{!! Form::text('durationfrom', null, ['class'=>'form-control', 'placeholder'=>__('reports.start')]) !!}
						    <span class="input-group-addon">-</span>
						    {!! Form::text('durationto', null, ['class'=>'form-control', 'placeholder'=>__('reports.end')]) !!}
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('phone', __('general.phone')) !!}
						{!! Form::tel('phone', null, ['class'=>'form-control', 'required' => false]) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('custom_table', __('reports.custom_table')) !!}
						{!! Form::select("custom_table", [null=>__('general.select_one')] + $filters['custom_table'], null, ["class" => "form-control", 'id'=> 'custom_table', 'required' => false]) !!}
					</div>
				</div>
			</div>

			<div class="checkbox">
                <label>
                    {!! Form::checkbox('showonlyterm', null, $filters['showonlyterm'], ['id'=>'showonlyterm']) !!}
                    {{__('reports.termination_status')}}
                </label>
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