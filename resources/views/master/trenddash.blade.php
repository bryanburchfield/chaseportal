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
	    <div class="col-sm-6 ">
	        <div class="card card-6" >
	            <div class="call_volume_details">
	                <h1 class="title">Call Volume</h1>
	                <p class="total mb_hide"></p>
	            </div>

	            <div class="btn-group btn-group-sm callvolume_inorout" role="group" aria-label="...">
	                <button data-type="inbound" type="button" class="btn btn-primary">Inbound</button>
	                <button data-type="outbound" type="button" class="btn btn-default">Outbound</button>
	            </div>

	            <div class="inbound inandout" style="height: 300px">
	                <canvas id="call_volume_inbound"></canvas>
	            </div>

	            <div class="outbound inandout " style="height: 300px">
	                <canvas id="call_volume_outbound"></canvas>
	            </div>
	        </div>
	    </div>

	    <div class="col-sm-6">
	        <div class="card card-6" >
	            <h1 class="title mb0">Service Level</h1>
	            <h2 class="avg_sl mb_hide"></h2>
	            <p class="descrip mt10 mb_hide">Handled/Total. Handled is answered with < 20 sec holdtime</p>

	            <div class="btn-group btn-group-sm callvolume_inorout" role="group" aria-label="...">
	            </div>
	            <div class="inbound inandout" style="height: 300px">
	                <canvas id="service_level"></canvas>
	            </div>
	        </div>
	    </div>
	</div>

	<div class="row">

	    <div class="col-sm-6 ">
	        <div class="card card-6" >
	            <h1 class="title mb0">Average Handle Time</h1>
	            <h2 class="avg_ht mb_hide"></h2>
	            <p class="descrip mt10 mb_hide">Talk Time + Hold Time + After Call Work / Total Calls</p><br>

	            <div class="btn-group btn-group-sm callvolume_inorout" role="group" aria-label="...">
	            </div>
	            <div class="inbound inandout" style="height: 300px">
	                <canvas id="avg_handle_time"></canvas>
	            </div>
	        </div>
	    </div>

	    <div class="col-sm-6 ">
	        <div class="card card-6" >
	            <h1 class="title mb0">Call Details</h1><br>
	            <h2 class="avg_tt mb_hide"></h2>
	            <div class="btn-group btn-group-sm callvolume_inorout" role="group" aria-label="...">
	            </div>
	            <div class="inbound inandout" style="height: 300px">
	                <canvas id="call_details"></canvas>
	            </div>
	        </div>
	    </div>
	</div>

	<div class="row">
	    <div class="col-sm-12 ">
	        <div class="card card-6" >
	            <h1 class="title mb0">Agent Call Times / Call Count</h1><br>
	            <h2 class="avg_cc mb_hide"></h2><br>
	            <h2 class="avg_ct mb_hide"></h2>
	            <!-- <div class="btn-group btn-group-sm callvolume_inorout" role="group" aria-label="...">
	            </div> -->
	            <div class="inbound inandout" style="height: 300px">
	                <canvas id="rep_talktime"></canvas>
	            </div>
	        </div>
	    </div>
	</div>
</div>

@include('shared.datepicker')
