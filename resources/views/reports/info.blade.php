@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')

    <a class="btn btn-primary btn_flt_rgt" href="{{ action('ReportController@index', ['report' => $report]) }}">{{__('tools.back')}}</a>
    <h3 class="heading">{{__('reports.' . $report)}}</h3>

	<div class="report_filters card col-sm-12">

        @for ($i = 1; $i <= $paragraphs; $i++)
            <p class="mb10">{{__('report_info.' . $report . '_' . $i)}}</p>
        @endfor

        @if (count($columns))
            <h5 class="mb10">{{__('report_info.columns')}}:</h5>
            @foreach ($columns as $column)
                <p class="mb5">
                    <b>{{__($column)}}</b> : {{__('report_info.' . Str::after($column, '.'))}}
                </p>
            @endforeach
        @endif

	</div><!-- end report_filters -->

@endsection