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
            <div class="card-3 card get_hgt2" id="total_calls">
                <div class="trend_indicator down">
                    <div class="trend_arrow arrow_down"></div>
                    <span></span>
                </div>

                <h1 class="title">Total Dials</h1>
                <h4 class="data total mt30"></h4>
                
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6 card_table_prt">
            <div class="card set_hgt2 flipping_card card-3b" id="total_contacts_card">
                <div class="front p20">
                    <div class="flip_card_btn"></div> 
                    <div class="trend_indicator down">
                        <div class="trend_arrow arrow_down"></div>
                        <span></span>
                    </div>
                    <h1 class="title">Total Contacts</h1>
                    <h4 class="data mt30" id="total_contacts"></h4>                    
                </div>

                <div class="back">
                    <div class="flip_card_btn"></div>
                    <div class="trend_indicator down">
                        <div class="trend_arrow arrow_down"></div>
                        <span></span>
                    </div>

                    <h1 class="title">Contact Rate</h1>
                    <h4 class="data mt30" id="contact_rate"></h4>
                    
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6 card_table_prt">
            <div class="card set_hgt2 flipping_card card-3b total_sales_card">

                <div class="front">
                    <div class="card_table">
                        <h1 class="title">Agent Sales</h1>
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
                    <div class="trend_indicator up">
                        <div class="trend_arrow arrow_up"></div>
                        <span></span>
                    </div>
                    <h1 class="title">Total Sales</h1>
                    <h4 class="data" id="total_sales"></h4>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6 card_table_prt">
            <div class="card set_hgt2 flipping_card card-3b sales_per_hour">
                <div class="front ">
                    <div class="card_table">
                        <h1 class="title">Conversion Rate</h1>
                        <h4 class="data" id="conversion_rate"></h4>
                        <div class="flip_card_btn"></div>                        
                    </div>
                </div>

                <div class="back">
                    <div class="flip_card_btn"></div>
                    <div class="">
                        <div class="trend_indicator down">
                            <div class="trend_arrow arrow_down"></div>
                            <span></span>
                        </div>
                        <h1 class="title">Sales Per Hour</h1>
                        <h4 class="data" id="sales_per_hour"></h4>
                    </div>
                </div>
               
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
        <div class="col-sm-3 card_table_prt get_hgt">
            <div class="card flipping_card card-3b">
                <div class="front ">
                    <div class="card_table">
                        <h1 class="title">Top 10 Agent Call Counts</h1>
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
                    <h1 class="title">Top 10 Agent Call Counts</h1>
                    <div class="inbound inandout mb0">
                        <canvas id="agent_call_count_graph"></canvas>
                    </div>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-9 card_table_prt ">
            <div class="card flipping_card card-3b mt70_mb set_hgt">
                <div class="front ">
                    <div class="card_table">
                        <h1 class="title">Top 10 Calls by Campaign</h1>
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
                    <h1 class="title">Top 10 Calls by Campaign</h1>
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="calls_by_campaign_graph"></canvas>
                    </div>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->
    
    </div>

    <div class="row">

        <div class="col-sm-3 card_table_prt ">
            <div class="card flipping_card card-3b mt70_mb ">

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

                <div class="back">
                    <h1 class="title">Avg Wait Time</h1>
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="avg_wait_time_graph"></canvas>
                    </div>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-9 card_table_prt ">
            <div class="card flipping_card card-3b set_hgt">
                <div class="front ">
                    <div class="card_table">
                        <h1 class="title">Top 10 Agent Talk Times</h1>
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
                    <h1 class="title">Top 10 Agent Talk Times</h1>
                    <div class="inbound inandout mb0">
                        <canvas id="agent_talk_time_graph"></canvas>
                    </div>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card card-12">
                <h1 class="title fw600">Agent Call Status</h1>
                <div class="inbound inandout" style="min-height:340px;">
                    <canvas id="agent_call_status"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
