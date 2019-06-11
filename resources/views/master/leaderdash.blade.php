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

	<div class="row leaderboard_main_row">

	    <div class="col-md-3 col-sm-4 leader_table_div_colm card_table_prt">
	        <div class="card plr0 leader_table_div card_table">
	            <h1 class="title mb0">Sales Leaderboard</h1>

	            <div class="table-responsive overflowauto">
	            	<table class="table table-striped salesleaderboardtable">
	            	    <tbody></tbody>
	            	</table>
	            </div>
	        </div>
	    </div>

	    <div class="col-md-9 col-sm-8 get_ldr_ht">
	        <div class="card card-12" >
	            <div class="call_volume_details">
	                <h1 class="title tac">Call Volume</h1><br>
	            </div>

	            <div class="inbound inandout" style="height: 300px">
	                <canvas id="call_volume"></canvas>
	            </div>
	        </div>

	        <div class="col-md-4 col-sm-12 pl0 match_height_4_gt">
	            <div class="card card-6 mb0" >
	                <div class="inbound inandout mb0">
	                    <canvas id="sales_per_campaign"></canvas>
	                </div>
	            </div>
	        </div>

	        <div class="col-md-4 col-sm-12 match_height_4_st">
	            <div class="total_calls_out" >
	                <h2>Total Outbound Calls</h2>
	                <p class="total"></p>
	            </div>

	            <div class="total_calls_in">
	                <h2>Total Inbound Calls</h2>
	                <p class="total"></p>
	            </div>
	        </div>

			<div class="col-md-4 col-sm-12 pr0 match_height_4_st card_table_prt">
	            <div class="card agent_sales_per_hour_card card_table set_hgt">
	                <h1 class="title">Agent Sales Per Hour</h1>
	                <table class="table table-condensed table-striped" id="agent_sales_per_hour">
	                    <thead>
	                        <tr>
	                            <th>Rep</th>
	                            <th>Sales Per Hour</th>
	                        </tr>
	                    </thead>
	                    <tbody></tbody>
	                </table>
	            </div>
	        </div>
	        
	    </div>
	</div>
</div>

@include('shared.datepicker')
