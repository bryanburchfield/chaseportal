@extends('layouts.report')
@section('title', 'Report')

@section('content')
	<h3 class="heading">Agent Time Sheet</h3>
	
	<div class="report_filters well col-sm-12">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form']) !!}

			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('fromdate', 'From') !!}
						<div class="input-group date">
							{!! Form::text('fromdate', null, ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true]) !!}
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
							{!! Form::text('todate', null, ['class'=>'form-control datetimepicker', 'required' => true, 'readonly'=>true]) !!}
							<span class="input-group-addon">
			                    <span class="glyphicon glyphicon-calendar">
			                    </span>
			                </span>
						</div>
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('reps', 'Rep') !!}
						{!! Form::select("reps[]", $reps, null, ["class" => "form-control multiselect", 'id'=> 'rep_select','multiple'=>true]) !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('skills', 'Skill') !!}
						{!! Form::select("skills[]", $skills, null, ["class" => "form-control multiselect", 'id'=> 'skill_select','multiple'=>true]) !!}
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
		{{ $call_details_table}}
	</div>
@endsection