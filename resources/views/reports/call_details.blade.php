@extends('layouts.report')
@section('title', 'Report')

@section('content')
	<h3 class="heading">Call Details</h3>
	<div class="report_filters card col-sm-12">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form']) !!}

			<div class="row">
				
				@if(Auth::user()->isMultiDb())
					@php $show_multi_db = 'show_multi_db'; @endphp
				@else
					@php $show_multi_db = ''; @endphp
				@endif

				<div class="col-sm-4 multi_db {{ $show_multi_db }}">
					<div class="form-group">
						<label>Database</label>
			            <select name="databases[]" id="database_select" multiple class="form-control multiselect" value="<?php if(isset($_POST['databases'])){echo $_POST['databases'];}?>">

							@foreach ($filters['db_list'] as $key => $value) {
				                <option value="{{$value}}">{{$key}}</option>
				            @endforeach

						</select>
			        </div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('fromdate', 'From') !!}
						<div class="input-group date">
							{!! Form::text('fromdate', $date = isset($_POST['fromdate']) ? $_POST['fromdate'] : $params['fromdate'], ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true ]) !!}
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
						{!! Form::label('campaigns', 'Campaigns') !!}
						{!! Form::select("campaigns[]", $filters['campaigns'], null, ["class" => "form-control multiselect", 'id'=> 'campaign_select','multiple'=>true]) !!}
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('callerids', 'Inbound Sources') !!}
						{!! Form::select("callerids[]", $filters['inbound_sources'], null, ["class" => "form-control multiselect", 'id'=> 'inbound_sources_select','multiple'=>true]) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('reps', 'Reps') !!}
						{!! Form::select("reps[]", $filters['reps'], null, ["class" => "form-control multiselect", 'id'=> 'rep_select','multiple'=>true]) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('call_statuses', 'Call Statuses') !!}
						{!! Form::select("call_statuses[]", $filters['call_statuses'], null, ["class" => "form-control multiselect", 'id'=> 'call_status_select','multiple'=>true]) !!}
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('call_type', 'Call Type') !!}
						{!! Form::select("call_type", $filters['call_types'], null, ["class" => "form-control", 'id'=> 'call_type']) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('durationfrom', 'Duration ( seconds )') !!}
						<div class="input-group">
							{!! Form::text('durationfrom', null, ['class'=>'form-control', 'placeholder'=>'Start']) !!}
						    <span class="input-group-addon">-</span>
						    {!! Form::text('durationto', null, ['class'=>'form-control', 'placeholder'=>'End']) !!}
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('phone', 'Phone') !!}
						{!! Form::tel('phone', null, ['class'=>'form-control', 'required' => false]) !!}
					</div>
				</div>
			</div>

			<div class="checkbox">
                <label>
                    {!! Form::checkbox('showonlyterm', null, false, ['id'=>'showonlyterm']) !!}
                    Show only termination status
                </label>
			</div>

			<div class='reporterrors'></div>

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