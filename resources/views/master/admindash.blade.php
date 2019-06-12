<div class="container-full mt20">
	<div class="row">
	    <div class="col-sm-9">
            <div class="filter_time_camp_dets">
                <p>
                    <span class="selected_datetime"></span> |
                    <span class="selected_campaign"></span>
                </p>
            </div>
	    </div>
	</div>

    <div class="row">
        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card" id="completed_calls">

                <h1 class="title">Completed Calls</h1>
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

        <div class="col-sm-3 col-xs-12">
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

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card avg_hold_time_card">
                <h1 class="title">Average Hold Time</h1>
                <h4 class="data" id="avg_hold_time"></h4>

                <div class="divider"></div>

                <div class="details tac">
                    <p class="data" id="total_hold_time"></p>
                    <p class="type">Total Hold Time</p>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->

        <div class="col-sm-3 col-xs-12">
            <div class="card-3 card abandon_calls_card">
                <h1 class="title">Abandoned Calls</h1>
                <h4 class="data count" id="abandon_calls"></h4>

                <div class="divider"></div>

                <div class="details tac">
                    <p class="data" id="abandon_rate"></p>
                    <p class="type">Abandon Rate</p>
                </div>
            </div><!-- end card -->
        </div><!-- end column -->
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="card-6 card" >
                <h1 class="title">Call Volume</h1>
                <div class="btn-group btn-group-sm callvolume_inorout" role="group" aria-label="...">
                    <button data-type="inbound" type="button" class="btn btn-primary">Inbound</button>
                    <button data-type="outbound" type="button" class="btn btn-default">Outbound</button>
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
            <div class="card-6 card" >
                <h1 class="title">Call Duration</h1>

                <div class="inandout">
                    <canvas id="call_duration"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3 card_table_prt">
            <div class="card agent_sales_per_hour_card card_table set_hgt">
                <h1 class="title">Agent Call Count</h1>
                <table class="table table-condensed table-striped" id="agent_call_count">
                    <thead>
                        <tr>
                            <th>Rep</th>
                            <th>Calls</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="col-sm-3 get_hgt">
            <div class="card">
                <p class="descrip">Handled/Total. Handled is answered with < 20 sec holdtime</p>
                <canvas id="service_level"></canvas>
            </div>
        </div>

        <div class="col-sm-3 card_table_prt">
            <div class="card agent_sales_per_hour_card card_table set_hgt">
                <h1 class="title">Agent Call Time</h1>
                <table class="table table-condensed table-striped" id="agent_calltime">
                    <thead>
                        <tr>
                            <th>Rep</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="col-sm-3 card_table_prt">
            <div class="card card_table set_hgt">
                <h2 class="card_title">REP AVG HANDLE TIME</h2>
                <table class="table table-condensed table-striped" id="rep_avg_handletime"></table>
            </div>
        </div>
    </div>
</div>

@include('shared.datepicker')
