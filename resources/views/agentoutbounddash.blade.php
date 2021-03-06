@extends('layouts.agentdash')

@section('title',  __('widgets.agent_dash'))

@section('content')

<div class="preloader"></div>
<input type="hidden" value="{{ $dateFilter }}" id="datefilter" name="datefilter">

<div class="container-fluid bg">
    <div class="container mt50">

        <div class="container-fluid">

            <div class="col-xs-7">
                <div class="filter_time_camp_dets">
                    <p>
                        <span class="selected_datetime"></span> |
                        <span class="selected_campaign"></span>
                    </p>
                </div>
            </div>

            <div class="filters col-sm-5">
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <span>{{__('general.date')}}</span>
                    </button>

                    @php
                        $selected_date_filter = $dateFilter;
                        if (!in_array($selected_date_filter, ['today', 'yesterday', 'week', 'last_week', 'month', 'last_month'])) {
                            $selected_date_filter = 'custom';
                        }
                    @endphp
                    <ul class="dropdown-menu date_filters">
                        <li {!! ($selected_date_filter == 'today') ? 'class="active"' : '' !!}><a href="#" data-datefilter="today">{{__('general.today')}}</a></li>
                        <li {!! ($selected_date_filter == 'yesterday') ? 'class="active"' : '' !!}><a href="#" data-datefilter="yesterday">{{__('general.yesterday')}}</a></li>
                        <li {!! ($selected_date_filter == 'week') ? 'class="active"' : '' !!}><a href="#" data-datefilter="week">{{__('general.this_week')}}</a></li>
                        <li {!! ($selected_date_filter == 'last_week') ? 'class="active"' : '' !!}><a href="#" data-datefilter="last_week">{{__('general.last_week')}}</a></li>
                        <li {!! ($selected_date_filter == 'month') ? 'class="active"' : '' !!}><a href="#" data-datefilter="month">{{__('general.this_month')}}</a></li>
                        <li {!! ($selected_date_filter == 'last_month') ? 'class="active"' : '' !!}><a href="#" data-datefilter="last_month">{{__('general.last_month')}}</a></li>
                    </ul>
                </div>

                <li class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <span><i class="fas fa-globe-americas"></i> Language</span>
                    </button>

                    <ul class="dropdown-menu lang_select stop-propagation">
                        <li><a class="dropdown-item" href="{{url('lang/en')}}">English</a></li>
                        <li><a class="dropdown-item" href="{{url('lang/es')}}"> Espa??ol</a></li>
                    </ul>
                </li>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card blue" id="total_outbound">
                    <h1 class="title">{{__('widgets.total_outbound')}}</h1>
                    <h4 class="data count total"></h4>
                </div><!-- end card -->
            </div><!-- end column -->

            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card orange" id="total_inbound">
                    <h1 class="title">{{__('widgets.total_inbound')}}</h1>
                    <h4 class="data count total"></h4>
                </div><!-- end card -->
            </div><!-- end column -->

            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card total_talktime_card green">
                    <h1 class="title">{{__('widgets.total_sales')}}</h1>
                    <h4 class="data" id="total_sales"></h4>
                </div><!-- end card -->
            </div><!-- end column -->

            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card avg_handle_time_card yellow">
                    <h1 class="title">{{__('widgets.sales_per_hour')}}</h1>
                    <h4 class="data" id="sales_per_hour"></h4>
                </div><!-- end card -->
            </div><!-- end column -->
        </div>

        <div class="row bdrless_card">
            <div class="col-sm-6 pl0">
                <div class="bdrcard">
                    <div class="col-sm-4 pr0">
                        <table class="table table-condensed ">
                            <tr class="bdrtop_none">
                                <th>{{__('widgets.call_types')}}</th>
                                <th class="tar"># {{__('widgets.call_status')}}</th>
                            </tr>

                            <tr>
                                <td>{{__('widgets.inbound')}}</td>
                                <td class="tar inbound_total"></td>
                            </tr>

                            <tr>
                                <td>{{__('widgets.manual')}}</td>
                                <td class="tar manual_total"></td>
                            </tr>

                            <tr>
                                <td>{{__('widgets.outbound')}}</td>
                                <td class="tar outbound_total"></td>
                            </tr>

                            <tr>
                                <td>{{__('widgets.total')}}</td>
                                <td class="tar total_calls"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-sm-8 pl0">
                        <div class="card-6 card">
                            <div class="inbound inandout" style="height:240px">
                                <canvas id="call_volume"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 pr0">
                <div class="bdrcard">
                    <div class="col-sm-4 pr0">
                        <table class="table table-condensed ">
                            <tr class="bdrtop_none">
                                <th>{{__('widgets.call')}}</th>
                                <th class="tar">{{__('widgets.talk_time')}}</th>
                            </tr>

                            <tr>
                                <td>{{__('widgets.call')}}</td>
                                <td class="tar call_total"></td>
                            </tr>

                            <tr>
                                <td>{{__('widgets.paused')}}</td>
                                <td class="tar paused_total"></td>
                            </tr>

                            <tr>
                                <td>{{__('widgets.waiting')}}</td>
                                <td class="tar waiting_total"></td>
                            </tr>

                            <tr>
                                <td>{{__('widgets.wrap_up')}}</td>
                                <td class="tar wrapup_total"></td>
                            </tr>

                            <tr>
                                <td>{{__('widgets.total')}}</td>
                                <td class="tar total_total"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-sm-8 pl0">
                        <div class="card-6 card">
                            <div class="inandout" style="height:240px">
                                <canvas id="rep_performance"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-9">
                <div class="card" style="height:280px">
                    <canvas id="call_status_count"></canvas>
                </div>
            </div>

            <div class="col-sm-3 ">
                <div class="card blue total_calls_card">
                    <h1 class="title">{{__('widgets.total_calls')}}</h1>
                    <h2 class="total_calls cnt"></h2>
                </div>
            </div>
        </div>

    </div>
</div>

@include('shared.datepicker')

@endsection