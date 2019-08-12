<div class="container-full mt20">
    <div class="row">
        <div class="col-sm-12">
            <div class="filter_time_camp_dets">
                <p>
                    <span class="selected_datetime"></span> | 
                    <span class="selected_campaign"></span>
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3 col-xs-6">
            <div class="card-3 card" id="total_calls">
                <div class="trend_indicator down">
                    <div class="trend_arrow arrow_down"></div>
                    <span></span>
                </div>

                <h1 class="title">Total Dials</h1>
                <h4 class="data total mt30"></h4>
                
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6">
            <div class="card-3 card get_hgt2" id="total_minutes">

                <div class="trend_indicator up">
                    <div class="trend_arrow arrow_up"></div>
                    <span></span>
                </div>

                <h1 class="title">Total Duration</h1>
                <h4 class="data total mt30"></h4>

            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6 card_table_prt">
            <div class="card set_hgt2 flipping_card card-3b">

                <div class="front ">
                    <div class="card_table2">
                        <h1 class="title">Avg Wait Time</h1>
                        <div class="flip_card_btn"></div>
                        <table class="table table-condensed table-striped" id="avg_wait_time">
                            <thead>
                                <tr>
                                    <th>Rep</th>
                                    <th>Campaign</th>
                                    <th>Avg Wait</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                </div>

                <div class="back sales_per_hour">

                    <div class="trend_indicator down">
                        <div class="trend_arrow arrow_down"></div>
                        <span></span>
                    </div>
                    <h1 class="title">Sales Per Hour</h1>
                    <h4 class="data" id="sales_per_hour"></h4>
                    <div class="flip_card_btn"></div> 
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6">
            <div class="card-3 card total_sales_card set_hgt2">
                <div class="trend_indicator up">
                    <div class="trend_arrow arrow_up"></div>
                    <span></span>
                </div>
                <h1 class="title">Total Sales</h1>
                <h4 class="data" id="total_sales"></h4>
            </div><!-- end card -->
        </div><!-- end column -->

    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="card-6 card outbound_default" >
                <h1 class="title">Call Volume</h1>

                <div class="outbound inandout">
                    <canvas id="call_volume_outbound"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card-6 card" >
                <h1 class="title">Call Duration in Minutes</h1><br><br><br>

                <div class="inandout">
                    <canvas id="call_duration"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3 card_table_prt set_hgt">
            <div class="card flipping_card card-3b">
                <div class="front ">
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
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 card_table_prt get_hgt">
            <div class="card flipping_card card-3b mt70_mb">
                <div class="front ">
                    <div class="card_table">
                        <h1 class="title">Calls by Campaign</h1>
                        <div class="flip_card_btn"></div>
                        <table class="table table-condensed table-striped" id="calls_by_campaign">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                </div>

                <div class="back">
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="calls_by_campaign_graph"></canvas>
                    </div>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 card_table_prt set_hgt">
            <div class="card flipping_card card-3b">
                <div class="front ">
                    <div class="card_table">
                        <h1 class="title">Agent Talk Time</h1>
                        <div class="flip_card_btn"></div>
                        <table class="table table-condensed table-striped" id="agent_talk_time">
                            <thead>
                                <tr>
                                    <th>Rep</th>
                                    <th>Campaign</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                </div>

                <div class="back">
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="agent_talk_time_graph"></canvas>
                    </div>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 card_table_prt set_hgt">
            <div class="card flipping_card card-3b mt70_mb">
                <div class="front ">
                    <div class="card_table">
                        <h1 class="title">Sales Per Hour</h1>
                        <div class="flip_card_btn"></div>
                        <table class="table table-condensed table-striped" id="sales_per_hour_per_rep">
                            <thead>
                                <tr>
                                    <th>Rep</th>
                                    <th>Campaign</th>
                                    <th>Sales</th>
                                    
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                </div>

                <div class="back">
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="sales_per_hour_per_rep_graph"></canvas>
                    </div>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->
    
    </div>
</div>

@include('shared.datepicker')