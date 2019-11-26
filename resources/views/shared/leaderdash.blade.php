<div class="row leaderboard_main_row">
    <div class="col-md-3 col-sm-4 leader_table_div_colm card_table_prt">
        <div class="card plr0 leader_table_div card_table card-3 mb0">
            <h1 class="title">{{__('widgets.sales_leaderboard')}}</h1>

            <div class="table-responsive overflowauto">
                <table class="table table-striped salesleaderboardtable">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-9 col-sm-8 get_ldr_ht mb0">
        <div class="card card-12">
            <div class="call_volume_details">
                <h1 class="title tac">{{__('widgets.call_volume')}}</h1><br>
            </div>

            <div class="inbound inandout mb60" style="height: 300px">
                <canvas id="call_volume"></canvas>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 card_table_prt get_hgt pl0">
            <div class="card flipping_card card-3b mbo">
                <div class="front">
                    <div class="card_table">
                        <h1 class="title">{{__('widgets.sales_per_camp')}}</h1>
                        <div class="flip_card_btn"></div>
                        <table class="table table-condensed table-striped" id="sales_per_campaign">
                            <thead>
                                <tr>
                                    <th>{{__('widgets.campaign')}}</th>
                                    <th>{{__('widgets.sales')}}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="back">
                    <h1 class="title">{{__('widgets.sales_per_camp')}}</h1>
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="sales_per_campaign_graph"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 set_hgt">
            <div class="total_calls_out">
                <h2>{{__('widgets.total_outbound_calls')}}</h2>
                <p class="total"></p>
            </div>
            <div class="total_calls_in">
                <h2>{{__('widgets.total_inbound_calls')}}</h2>
                <p class="total"></p>
            </div>
            <br><br>
        </div>

        <div class="col-md-4 col-sm-12 card_table_prt pl0 set_hgt">
            <div class="card flipping_card card-3b mbo">
                <div class="front">
                    <div class="card_table">
                        <h1 class="title">{{__('widgets.agent_sales_per_hour')}}</h1>
                        <div class="flip_card_btn"></div>
                        <table class="table table-condensed table-striped" id="agent_sales_per_hour">
                            <thead>
                                <tr>
                                    <th>{{__('widgets.rep')}}</th>
                                    <th>{{__('widgets.sales_per_hour')}}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="back">
                    <h1 class="title">{{__('widgets.agent_sales_per_hour')}}</h1>
                    <div class="flip_card_btn"></div>
                    <div class="inbound inandout mb0">
                        <canvas id="agent_sales_per_hour_graph"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>