@extends('layouts.report')
@section('title', 'Report')

@section('content')
	<h3 class="heading">Lead Inventory Subcampaign</h3>

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
						
						{!! Form::label('campaign', 'Campaign') !!}
						{!! Form::select("campaign", $filters['campaign'], null, ["class" => "form-control", 'id'=> 'campaign_select']) !!}
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
	<h4 class="total_leads"></h4><br>
	<h4 class="available_leads"></h4>
@endsection