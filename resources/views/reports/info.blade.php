@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')

    <a class="btn btn-primary float-right" href="{{ action('ReportController@index', ['report' => $report]) }}">{{__('tools.back')}}</a>
    <h3 class="heading">{{__('reports.' . $report)}}</h3>

	<div class="row">
		<div class="col-sm-4">
			<div class="card report_info_columns">
				@if (count($columns))
				    <h4 class="mb10"><b>{{__('report_info.columns')}}</b></h4>
				    @foreach ($columns as $column)
				        <p class="mb5">
				            <b>{{__($column)}}</b> : {{__('report_info.' . Str::after($column, '.'))}}
				        </p>
				    @endforeach
				@endif
			</div>
		</div>

		<div class="report_desc col-sm-8 ">

	        @for ($i = 1; $i <= $paragraphs; $i++)
	            <p class="mb10">{{__('report_info.' . $report . '_' . $i)}}</p>
	        @endfor
		</div><!-- end report_filters -->
	</div>

@endsection