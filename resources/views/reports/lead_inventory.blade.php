@extends('layouts.report')
@section('title', 'Report')

@section('content')
	<h3 class="heading">Lead Inventory</h3>

	<div class="report_filters well col-sm-12">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form']) !!}

			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('campaign', 'Campaign') !!}
						{!! Form::select("campaign[]", $campaigns, null, ["class" => "form-control multiselect", 'id'=> 'campaign_select','multiple'=>true]) !!}
					</div>
				</div>
			</div>

			<div class='reporterrors'>
				@include('shared.reporterrors')
			</div>

			{!! Form::hidden('report', $report, ['id'=>'report']) !!}
			{!! Form::submit('Run Report', ['class'=>'btn btn-primary mb0']) !!}

		{!! Form::close() !!}
	</div>
@endsection