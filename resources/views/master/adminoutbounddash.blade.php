<div class="container-full mt20">
    <div class="row">
        <div class="col-sm-3 col-xs-6">
            <div class="card-3 card" id="total_calls">

                <h1 class="title">Total Calls</h1>
                <h4 class="data count total"></h4>

                <div class="divider"></div>

                <div class="inbound">
                    <p class="data inbound"></p>
                    <p class="type">Inbound</p>
                </div>

                <div class="outbound">
                    <p class="data outbound"></p>
                    <p class="type">Outbound</p>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6">
            <div class="card-3 card" id="total_minutes">

                <h1 class="title">Total Minutes</h1>
                <h4 class="data count total"></h4>

                <div class="divider"></div>

                <div class="inbound">
                    <p class="data inbound"></p>
                    <p class="type">Inbound</p>
                </div>

                <div class="outbound">
                    <p class="data outbound"></p>
                    <p class="type">Outbound</p>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6">
            <div class="card-3 card sales_per_hour_card">
                <h1 class="title">Sales Per Hour</h1>
                <h4 class="data" id="sales_per_hour"></h4>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6">
            <div class="card-3 card total_sales_card">
                <h1 class="title">Total Sales</h1>
                <h4 class="data count" id="total_sales"></h4>
            </div><!-- end card -->
        </div><!-- end column -->

    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="card-6 card outbound_default">
                <h1 class="title">Call Volume</h1>
                <div class="btn-group btn-group-sm callvolume_inorout" role="group" aria-label="...">
                    <button data-type="outbound" type="button" class="btn btn-primary">Outbound</button>
                    <button data-type="inbound" type="button" class="btn btn-default">Inbound</button>
                </div>

                <div class="inbound inandout">
                    <canvas id="call_volume_inbound"></canvas>
                </div>

                <div class="outbound inandout">
                    <canvas id="call_volume_outbound"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card-6 card">
                <h1 class="title">Call Duration</h1><br><br><br>

                <div class="inandout">
                    <canvas id="call_duration"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3 col-xs-6 card_table_prt">
            <div class="card agent_call_count_card card_table set_hgt">
                <h1 class="title">Agent Call Count</h1>
                <table class="table table-condensed table-striped" id="agent_call_count">
                    <thead>
                        <tr>
                            <th>Rep</th>
                            <th>Calls</th>
                            <th>Calls Per Hour</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="col-sm-3 col-xs-6 pl0 get_hgt ">
            <div class="card card-6 mb0">
                <!-- <h1 class="title mb0">Calls by Campaign</h1> -->
                <div class="inbound inandout mb0">
                    <canvas id="calls_by_campaign"></canvas>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6 card_table_prt">
            <div class="card agent_talk_time_card card_table set_hgt">
                <h1 class="title">Agent Talk Time</h1>
                <table class="table table-condensed table-striped" id="agent_talk_time">
                    <thead>
                        <tr>
                            <th>Rep</th>
                            <th>Time</th>
                            <th>Avg</th>
                        </tr>
                    </thead>
                </table>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-6 card_table_prt">
            <div class="card sales_per_hour_per_rep_card card_table set_hgt">
                <h1 class="title">Sales Per Hour Per Rep</h1>
                <table class="table table-condensed table-striped" id="sales_per_hour_per_rep">
                    <thead>
                        <tr>
                            <th>Rep</th>
                            <th>Sales</th>
                            <th>Per Hour</th>
                        </tr>
                    </thead>
                </table>
            </div><!-- end card -->
        </div><!-- end column -->



    </div><!-- end continer mt50 -->
</div>

@include('shared.datepicker')