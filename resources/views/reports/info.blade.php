@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')

    <a class="btn btn-primary btn_flt_rgt" href="{{ action('ReportController@index', ['report' => $report]) }}">{{__('tools.back')}}</a>
    <h3 class="heading">{{__('reports.' . $report)}}</h3>

	<div class="col-sm-4 card report_info_columns">
		@if (count($columns))
		    <h4 class="mb10"><b>{{__('report_info.columns')}}</b></h4>
		    @foreach ($columns as $column)
		        <p class="mb5">
		            <b>{{__($column)}}</b> : {{__('report_info.' . Str::after($column, '.'))}}
		        </p>
		    @endforeach
		@endif
	</div>

		<div class="report_desc col-sm-8 mt20 ">

	        @for ($i = 1; $i <= $paragraphs; $i++)
	            <p class="mb10">{{__('report_info.' . $report . '_' . $i)}}</p>
	        @endfor
		</div><!-- end report_filters -->

@endsection