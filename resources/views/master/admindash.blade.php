<div class="container-full mt20">
    <div class="row">
        <div class="col-sm-12">
            <div class="filter_time_camp_dets">
                <p></p>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card" id="calls_offered">

                <div class="trend_indicator">
                    <div class="trend_arrow"></div>
                    <span></span>
                </div>               
                <h1 class="title">Calls Offered</h1>
                <h4 class="data total mt30"></h4>

            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card" id="calls_answered">

                <div class="trend_indicator">
                    <div class="trend_arrow"></div>
                    <span></span>
                </div>
               
                <h1 class="title">Calls Answered</h1>
                <h4 class="data total mt30"></h4>

            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card" id="missed_calls">

                <div class="trend_indicator">
                    <div class="trend_arrow"></div>
                    <span></span>
                </div>

                <h1 class="title">Missed Calls</h1>
                <h4 class="data total"></h4>
                
                <div class="divider"></div>
                
                <div class="inbound">
                    <p class="data abandoned"></p>
                    <p class="type">Abandoned</p>
                </div>

                <div class="outbound">
                    <p class="data voicemails"></p>
                    <p class="type">Voicemails</p>
                </div>

            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card total_sales">

                <div class="trend_indicator">
                    <div class="trend_arrow"></div>
                    <span></span>
                </div>
                <h1 class="title">Total Sales</h1>
                <h4 class="data mt30" id="total_sales"></h4>
            </div><!-- end card -->
        </div><!-- end column -->
        
    </div>

    <div class="row">

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card" id="avg_talk_time">

                <div class="trend_indicator">
                    <div class="trend_arrow"></div>
                    <span></span>
                </div>

                <h1 class="title">Avgerage Talk Time</h1>
                <h4 class="data total "></h4>

                <div class="divider"></div>

                <div class="inbound">
                    <p class="data lowest"></p>
                    <p class="type">Lowest</p>
                </div>

                <div class="outbound">
                    <p class="data highest"></p>
                    <p class="type">Highest</p>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card avg_hold_time_card">
                <div class="trend_indicator">
                    <div class="trend_arrow"></div>
                    <span></span>
                </div>

                <h1 class="title">Average Hold Time</h1>
                <h4 class="data" id="avg_hold_time"></h4>

                <div class="divider"></div>

                <div class="inbound">
                    <p class="data lowest"></p>
                    <p class="type">Lowest</p>
                </div>

                <div class="outbound">
                    <p class="data highest"></p>
                    <p class="type">Highest</p>
                </div>

            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card avg_handle_time_card">
                <div class="trend_indicator">
                    <div class="trend_arrow"></div>
                    <span></span>
                </div>

                <h1 class="title">Average Handle Time</h1>
                <h4 class="data" id="avg_handle_time"></h4>

                <div class="divider"></div>

                <div class="inbound">
                    <p class="data lowest"></p>
                    <p class="type">Lowest</p>
                </div>

                <div class="outbound">
                    <p class="data highest"></p>
                    <p class="type">Highest</p>
                </div>

            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card abandon_calls_card">

                <div class="trend_indicator">
                    <div class="trend_arrow"></div>
                    <span></span>
                </div>
                <h1 class="title">Abandoned Rate</h1>
                <h4 class="data" id="abandon_rate"></h4>

                <div class="divider"></div>

                <div class="details tac">
                    <p class="data" id="abandon_calls"></p>
                    <p class="type">Abandon Calls</p>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->
        
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="card-6 card" >
                <h1 class="title">Call Volume</h1>

                <div class="inbound inandout">
                    <canvas id="call_volume_inbound"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card-6 card" >
                <h1 class="title mb42">Call Duration in Minutes</h1><br><br><br>

                <div class="inandout">
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
                        <h1 class="title">Agent Call Count</h1>
                        <div class="flip_card_btn"></div>
                        <table class="table table-condensed table-striped" id="agent_call_count">
                            <thead>
                                <tr>
                                    <th>Rep</th>
                                    <th>Campaign</th>
                                    <th>Calls</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                </div>

                <div class="back">
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="agent_call_count_graph"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-3 set_hgt ">
            <div class="card card-3b mt70_mb">
                <h1 class="title">Service Level</h1>
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
                        <h3>Change Answered Time</h3>
                        <a href="20">20 seconds</a>
                        <a href="30">30 seconds</a>
                        <a href="40">40 seconds</a>
                        <a href="50">50 seconds</a>
                        <a href="60">60 seconds</a>
                    </div>
                </div>
                <p class="descrip">Handled/Total. Handled is answered with &lt; <span class="answer_secs">20</span> sec holdtime</p>
                <canvas id="service_level"></canvas>
            </div>
        </div>

        <div class="col-sm-3 get_hgt card_table_prt ">
            <div class="card flipping_card card-3b mt120_mb">
                <div class="front ">
                    <div class="card_table">
                        <h1 class="title">Agent Call Time</h1>
                        <div class="flip_card_btn"></div>
                        <table class="table table-condensed table-striped" id="agent_calltime">
                            <thead>
                                <tr>
                                    <th>Rep</th>
                                    <th>Campaign</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                </div>

                <div class="back">
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="agent_calltime_graph"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-3 set_hgt card_table_prt">
            <div class="card flipping_card card-3b">
                <div class="front ">
                    <div class="card_table">
                        <h1 class="title">REP AVG HANDLE TIME</h1>
                        <div class="flip_card_btn"></div>
                        <table class="table table-condensed table-striped" id="rep_avg_handletime">
                            <thead>
                                <tr>
                                    <th>Rep</th>
                                    <th>Campaign</th>
                                    <th>Avg</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                </div>

                <div class="back">
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="rep_avg_handletime_graph"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('shared.datepicker')
