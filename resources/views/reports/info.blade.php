@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')

    <a class="btn btn-primary btn_flt_rgt" href="{{ action('ReportController@index', ['report' => $report]) }}">{{__('tools.back')}}</a>
    <h3 class="heading">{{__('reports.' . $report)}}</h3>

	<div class="report_filters card col-sm-12">

        @foreach ($report_info as $paragraph)
            <p class="mb10">{{$paragraph}}</p>
        @endforeach

	</div><!-- end report_filters -->

@endsection