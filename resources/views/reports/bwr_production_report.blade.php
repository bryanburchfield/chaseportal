@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')
	<a href="{{ action('ReportController@info', ['report' => $report]) }}" class="btn btn-primary btn-sm flt_rgt"><i class="fas fa-info-circle"></i> Info</a>
	<h3 class="heading">{{__('reports.production_report')}}</h3>

	<div class="report_filters card col-sm-12 fc_style py-4 px-3">
		{!! Form::open(['method'=>'POST', 'url'=> '#', 'name'=>'report_filter_form', 'id'=>$report, 'class'=>'report_filter_form query_dates_first']) !!}

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
                
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('data_sources_primary', __('reports.data_source_primary')) !!}
                        {!! Form::select("data_sources_primary[]", $filters['data_sources_primary'], null, ["class" => "form-control multiselect", 'id'=> 'data_source_primary_select','multiple'=>true]) !!}
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('data_sources_secondary', __('reports.data_source_secondary')) !!}
                        {!! Form::select("data_sources_secondary[]", $filters['data_sources_secondary'], null, ["class" => "form-control multiselect", 'id'=> 'data_source_secondary_select','multiple'=>true]) !!}
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('programs', __('reports.program')) !!}
                        {!! Form::select("programs[]", $filters['programs'], null, ["class" => "form-control multiselect", 'id'=> 'program_select','multiple'=>true]) !!}
                    </div>
                </div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('skills', __('reports.skill')) !!}
						{!! Form::select("skills[]", $filters['skills'], null, ["class" => "form-control multiselect", 'id'=> 'skill_select','multiple'=>true]) !!}
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