<div class="container mt20">

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

	    <div class="col-sm-3">
	        <div class="card plr0">
	            <h1 class="title mb0">Top 10 Leaderboard</h1>

	            <table class="table table-striped">
	                <tbody></tbody>
	            </table>
	        </div>
	    </div>
	    <div class="col-sm-9 nopad">
	        <div class="card card-12" >
	            <div class="call_volume_details">
	                <h1 class="title tac">Call Volume</h1><br>
	            </div>

	            <div class="inbound inandout" style="height: 300px">
	                <canvas id="call_volume"></canvas>
	            </div>
	        </div>

	        <div class="col-sm-4 pl0">
	            <div class="card card-6" >
	                <!-- <h1 class="title mb0">Calls by Campaign</h1> -->

	                <div class="inbound inandout">
	                    <canvas id="calls_by_campaign"></canvas>
	                </div>
	            </div>
	        </div>

	        <div class="col-sm-4">
	            <div class="total_calls_out" >
	                <h2>Total Outbound Calls</h2>
	                <p class="total"></p>
	            </div>

	            <div class="total_calls_in">
	                <h2>Total Inbound Calls</h2>
	                <p class="total"></p>
	            </div>
	        </div>

	       <div class="col-sm-4 pr0">
	            <div class="card card-6" >
	                <!-- <h1 class="title mb0">Agent Calls by Campaign</h1> -->

	                <div class="inbound inandout">
	                    <canvas id="agent_calls_by_campaign"></canvas>
	                </div>
	            </div>
	        </div>

	    </div>
	</div>
</div>

@include('shared.datepicker')
