@extends('layouts.report')
@section('title', __('general.reports'))

@section('content')
    <a href="{{ action('ReportController@info', ['report' => $report]) }}" class="btn btn-primary btn-sm flt_rgt"><i
            class="fas fa-info-circle"></i> Info</a>
    <h3 class="heading">{{ __('reports.callerid_swaps') }}</h3>

    <div class="report_filters card col-sm-12">
        {!! Form::open(['method' => 'POST', 'url' => '#', 'name' => 'report_filter_form', 'id' => $report, 'class' => 'report_filter_form fc_style']) !!}

        <div class="row">

            @include('shared.report_db_menu')

            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('fromdate', __('reports.from')) !!}
                    <div class="input-group date">
                        {!! Form::text('fromdate', $params['fromdate'], ['class' => 'form-control datetimepicker fromdate', 'required' => true, 'autocomplete' => 'off']) !!}
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
                        {!! Form::text('todate', $params['todate'], ['class' => 'form-control datetimepicker todate', 'required' => true, 'autocomplete' => 'off']) !!}
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar">
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('flag_type', __('reports.flag_type')) !!}
                    {!! Form::select('flag_type', $filters['flag_type'], null, ['class' => 'form-control', 'id' => 'flag_type']) !!}
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('phone', __('general.phone') . ' (' . __('reports.optional') . ')') !!}
                    {!! Form::tel('phone', null, ['class' => 'form-control', 'required' => false]) !!}
                </div>
            </div>

        </div>

        <div class="alert alert-danger report_errors"></div>

        {!! Form::hidden('report', $report, ['id' => 'report']) !!}
        {!! Form::submit(__('reports.run_report'), ['class' => 'btn btn-primary mb0']) !!}
        {!! Form::close() !!}
    </div><!-- end report_filters -->

    @include('reports.report_tools_inc')

    <div class="table-responsive report_table {{ $report }}">
        @include('shared.reporttable')
    </div>

    @include('reports.report_warning_inc')
@endsection
