@extends('layouts.agentdash')

@section('title', __('widgets.agent_dash'))

@section('content')

<div class="preloader"></div>
<input type="hidden" value="{{ $dateFilter }}" id="datefilter" name="datefilter">

<div class="container-fluid bg">
    <div class="container mt50">

        <div class="container-fluid">
            <div class="col-xs-7 pl0">
                <div class="filter_time_camp_dets">
                    <p>
                        <span class="selected_datetime"></span> |
                        <span class="selected_campaign"></span>
                    </p>
                </div>
            </div>

            <div class="filters  col-sm-5">
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <span>{{__('general.interaction')}}</span>
                    </button>

                    <ul class="dropdown-menu filter_campaign stop-propagation">
                        <div class="form-group mb0">
                            <input type="text" class="form-control campaign_search" placeholder="{{__('general.search')}}">
                            <input type="hidden" class="campaign_search_url" value="/agentcampaigndashboard/campaign_search">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block select_campaign"><i class="glyphicon glyphicon-ok"></i> {{__('general.submit')}}</button>

                        @foreach($campaign_list as $campaign)
                            <div class="checkbox">
                                <label class="campaign_label">
                                    <input class="campaign_group" required type="checkbox" {{ $campaign['selected'] == 1 ? "checked" : '' }} value="{{$campaign['value']}}" name="campaigns">
                                    <span>
                                        {{$campaign['name']}}
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </ul>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <span>{{__('general.date')}}</span>
                    </button>

                    <?php
                        $selected_date_filter = $dateFilter;
                        if (!in_array($selected_date_filter, ['today', 'yesterday', 'week', 'last_week', 'month', 'last_month'])) {
                            $selected_date_filter = 'custom';
                        }
                    ?>
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
                        <li><a class="dropdown-item" href="{{url('lang/es')}}"> Español</a></li>
                    </ul>
                </li>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card blue" id="total_inbound">
                    <h1 class="title">{{__('widgets.total_inbound_calls')}}</h1>
                    <h4 class="data count total"></h4>
                </div><!-- end card -->
            </div><!-- end column -->

            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card orange" id="handled_calls">
                    <h1 class="title">{{__('widgets.handled_calls')}}</h1>
                    <h4 class="data count total"></h4>
                </div><!-- end card -->
            </div><!-- end column -->

            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card total_talktime_card green">
                    <h1 class="title">{{__('widgets.total_talk_time')}}</h1>
                    <h4 class="data" id="total_talktime"></h4>
                </div><!-- end card -->
            </div><!-- end column -->

            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card avg_handle_time_card yellow">
                    <h1 class="title">{{__('widgets.avg_handle_time')}}</h1>
                    <h4 class="data" id="avg_handle_time"></h4>
                </div><!-- end card -->
            </div><!-- end column -->
        </div>
        
        <div class="row bdrless_card max_height350">
            <div class="col-sm-12 pl0 pr0">
                <div class="bdrcard overflow_none" >
                    <div class="col-sm-5 campaign_totals pl0 pr0" style="height:330px">
                        <table class="table table-condensed campaign_totals_table" >
                            <thead>
                                <tr class="bdrtop_none">
                                    <th>{{__('widgets.inbound_service_queue')}}</th>
                                    <th class="tar">{{__('widgets.numb_of_calls')}}</th>
                                    <th>{{__('widgets.abandoned')}}</th>
                                    <th>{{__('widgets.voicemails')}}</th>
                                </tr>
                            </thead>

                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="col-sm-7 pl0">
                        <div class="card-6 card">
                            <div class="inbound inandout" style="height:330px">
                                <canvas id="campaign_calls"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row bdrless_card">
            <div class="col-sm-8 pl0">
                <div class="bdrcard" style="height:285px">
                <table class="table table-condensed campaign_stats_table table-striped">
                        <thead>
                            <tr>
                                <th>{{__('widgets.inbound_service_queue')}}</th>
                                <th>{{__('widgets.avg_talk_time')}}</th>
                                <th>{{__('widgets.avg_hold_time')}}</th>
                                <th>{{__('widgets.avg_handle_time')}}</th>
                                <th>{{__('widgets.drop_rate')}}</th>
                            </tr>
                        </thead>

                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="col-sm-4 pr0">
                <div class="card stats_card p0" style="height:285px">
                    <h1 class="title mb20 ">{{__('widgets.stats')}}</h1>

                    <div class="stats">
                        <div class="handled_stats stats_chart col-sm-6 p0">
                            <h4>{{__('widgets.handled')}}</h4>
                            <div class="outer rep">
                                <div class="rep rep_handled inner"></div>
                            </div>
                            <div class="total"></div>
                            <div class="outer team">
                                <div class="team tot_handled inner"></div>
                            </div>
                            <div class="total"></div>
                        </div>

                        <div class="avg_handle_time_stats stats_chart col-sm-6 pr0">
                            <h4>{{__('widgets.avg_handle_time')}}</h4>
                            <div class="outer rep">
                                <div class="rep rep_avg_handle_time inner"></div>
                            </div>
                            <div class="total"></div>
                            <div class="outer team">
                                <div class="team rep_talk_time inner"></div>
                            </div>
                            <div class="total"></div>
                        </div>

                        <div class="interactions_stats stats_chart col-sm-6 p0">
                            <h4>{{__('widgets.successful_interactions')}}</h4>
                            <div class="outer rep">
                                <div class="rep rep_sales inner" ></div>
                            </div>
                            <div class="total"></div>
                            <div class="outer team">
                                <div class="team total_sales inner"></div>
                            </div>
                            <div class="total"></div>
                        </div>

                        <div class="stats_legend col-sm-12 mt30 pl0">
                            <div class="stats_item"><span class="repstats"></span>{{__('widgets.my_stats')}}</div>
                            <div class="stats_item"><span class="teamstats"></span>{{__('widgets.team_stats')}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card" style="height:370px">
                    <canvas id="calls_by_camp"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@include('shared.datepicker')

@endsection