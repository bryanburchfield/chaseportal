<div class="row display-flex">

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="calls_offered">

            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('widgets.calls_offered')}}</h1>
            <h4 class="data total mt-3"></h4>

        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="calls_answered">

            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>

            <h1 class="title">{{__('widgets.calls_answered')}}</h1>
            <h4 class="data total mt-3"></h4>

        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="missed_calls">

            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>

            <h1 class="title">{{__('widgets.missed_calls')}}</h1>
            <h4 class="data total"></h4>

            <div class="divider"></div>

            <div class="d-flex justify-content-between">
                <div class="inbound flt_lft">
                    <p class="data abandoned"></p>
                    <p class="type">{{__('widgets.abandoned')}}</p>
                </div>

                <div class="outbound flt_rgt">
                    <p class="data voicemails"></p>
                    <p class="type">{{__('widgets.voicemails')}}</p>
                </div>
            </div>

        </div><!-- end card -->
    </div><!-- end column -->


    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card total_sales">

            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('widgets.total_conversions')}}</h1>
            <h4 class="data mt-3" id="total_sales"></h4>
        </div><!-- end card -->
    </div><!-- end column -->
</div>

<div class="row display-flex">

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="avg_talk_time">

            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>

            <h1 class="title">{{__('widgets.avg_talk_time')}}</h1>
            <h4 class="data total "></h4>

            <div class="divider"></div>

            <div class="d-flex justify-content-between">
                <div class="inbound flt_lft">
                    <p class="data lowest"></p>
                    <p class="type">{{__('widgets.lowest')}}</p>
                </div>

                <div class="outbound flt_rgt">
                    <p class="data highest"></p>
                    <p class="type">{{__('widgets.highest')}}</p>
                </div>
            </div>
        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card avg_hold_time_card">
            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>

            <h1 class="title">{{__('widgets.avg_hold_time')}}</h1>
            <h4 class="data" id="avg_hold_time"></h4>

            <div class="divider"></div>

            <div class="d-flex justify-content-between">
                <div class="inbound flt_lft">
                    <p class="data lowest"></p>
                    <p class="type">{{__('widgets.lowest')}}</p>
                </div>

                <div class="outbound flt_rgt">
                    <p class="data highest"></p>
                    <p class="type">{{__('widgets.highest')}}</p>
                </div>
            </div>
        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">

        <div class="card-3 card avg_handle_time_card">
            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>

            <h1 class="title">{{__('widgets.avg_handle_time')}}</h1>
            <h4 class="data" id="avg_handle_time"></h4>

            <div class="divider"></div>

            <div class="d-flex justify-content-between">
                <div class="inbound flt_lft">
                    <p class="data lowest"></p>
                    <p class="type">{{__('widgets.lowest')}}</p>
                </div>

                <div class="outbound flt_rgt">
                    <p class="data highest"></p>
                    <p class="type">{{__('widgets.highest')}}</p>
                </div>
            </div>
        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card abandon_calls_card">

            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('widgets.abandon_rate')}}</h1>
            <h4 class="data" id="abandon_rate"></h4>

            <div class="divider"></div>

            <div class="details tac">
                <p class="data" id="abandon_calls"></p>
                <p class="type">{{__('widgets.abandon_calls')}}</p>
            </div>
        </div><!-- end card -->
    </div><!-- end column -->
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="card-6 card">
            <h1 class="title">{{__('widgets.call_volume')}}</h1>

            <div class="inbound inandout cb mt-4">
                <canvas id="call_volume_inbound"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card-6 card">
            <h1 class="title">{{__('widgets.call_duration_minutes')}}</h1>

            <div class="inandout mt-4">
                <canvas id="call_duration"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-sm-3 set_hgt card_table_prt ">
        <div class="card flipping_card card-3b">
            <div class="front">
                <div class="card_table">
                    <h1 class="title">{{__('widgets.top_ten_agent_call_counts')}}</h1>
                    <div class="flip_card_btn"></div>
                    <table class="table table-condensed table-striped" id="agent_call_count">
                        <thead>
                            <tr>
                                <th>{{__('widgets.rep')}}</th>
                                <th>{{__('widgets.campaign')}}</th>
                                <th>{{__('widgets.calls')}}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="back">
                <h1 class="title">{{__('widgets.top_ten_agent_call_counts')}}</h1>
                <div class="flip_card_btn"></div>
                <div class="inbound inandout mb0">
                    <canvas id="agent_call_count_graph"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-3 set_hgt ">
        <div class="card card-3b mbpb0">
            <h1 class="title">{{__('widgets.service_level')}}</h1>
            <!-- three dot menu -->
            <div class="card_dropdown mv_left">
                <!-- three dots -->
                <ul class="card_dropbtn icons btn-left showLeft">
                    <li></li>
                    <li></li>
                    <li></li>
                </ul>
                <!-- menu -->
                <div id="card_dropdown" class="card_dropdown-content service_level_time">
                    <h3>{{__('widgets.change_answered_time')}}</h3>
                    <a href="20">20 {{__('widgets.seconds')}}</a>
                    <a href="30">30 {{__('widgets.seconds')}}</a>
                    <a href="40">40 {{__('widgets.seconds')}}</a>
                    <a href="50">50 {{__('widgets.seconds')}}</a>
                    <a href="60">60 {{__('widgets.seconds')}}</a>
                </div>
            </div>
            <p class="descrip">{{__('widgets.handled_total')}} &lt; <span class="answer_secs">20</span>
                {{__('widgets.sec_holdtime')}}</p>
            <canvas id="service_level"></canvas>
        </div>
    </div>

    <div class="col-sm-3 get_hgt card_table_prt ">
        <div class="card flipping_card card-3b ">
            <div class="front ">
                <div class="card_table">
                    <h1 class="title">{{__('widgets.top_ten_agent_call_times')}}</h1>
                    <div class="flip_card_btn"></div>
                    <table class="table table-condensed table-striped" id="agent_calltime">
                        <thead>
                            <tr>
                                <th>{{__('widgets.rep')}}</th>
                                <th>{{__('widgets.campaign')}}</th>
                                <th>{{__('widgets.duration')}}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="back">
                <h1 class="title">{{__('widgets.top_ten_agent_call_times')}}</h1>
                <div class="flip_card_btn"></div>
                <div class="inbound inandout mb0">
                    <canvas id="agent_calltime_graph"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-3 set_hgt card_table_prt">
        <div class="card flipping_card card-3b mbpb0">
            <div class="front ">
                <div class="card_table">
                    <h1 class="title">{{__('widgets.rep_avg_handletime')}}</h1>
                    <div class="flip_card_btn"></div>
                    <table class="table table-condensed table-striped" id="rep_avg_handletime">
                        <thead>
                            <tr>
                                <th>{{__('widgets.rep')}}</th>
                                <th>{{__('widgets.campaign')}}</th>
                                <th>{{__('widgets.avg')}}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="back">
                <h1 class="title">{{__('widgets.rep_avg_handletime')}}</h1>
                <div class="flip_card_btn"></div>
                <p class="descrip">{{__('widgets.max_handle_time')}}: <span class="max_handle_time"></span> .
                    {{__('widgets.chart_avg_based')}}</p>
                <canvas id="rep_avg_handletime_graph"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-3 set_hgt card_table_prt ">
        <div class="card flipping_card card-3b">
            <div class="front p20 mbp35">
                <h1 class="title">{{__('widgets.top_ten_dispos')}}</h1>
                <div class="flip_card_btn"></div>
                <div class="inbound inandout mb0">
                    <canvas id="dispositions_graph"></canvas>
                </div>
            </div>

            <div class="back">
                <h1 class="title">{{__('widgets.top_ten_agent_dispos')}}</h1>
                <div class="flip_card_btn"></div>
                <div class="inbound inandout mb0">
                    <canvas id="agent_dispositions_graph"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-9">
        <div class="card card-12">
            <h1 class="title">{{__('widgets.agent_call_status')}}</h1>
            <div class="inbound inandout cb" style="min-height:340px;">
                <canvas id="agent_call_status"></canvas>
            </div>
        </div>
    </div>
</div>