
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
	    <div class="col-sm-3 col-xs-12">
	        <div class="card-3 card" id="connect">

	            <div class="trend_indicator">
	                <div class="trend_arrow"></div>
	                <span></span>
	            </div>
	            <h1 class="title">{{__('widgets.connect')}} %</h1>
	            <h4 class="data total mt-3"></h4>

	        </div><!-- end card -->
	    </div><!-- end column -->

	    <div class="col-sm-3 col-xs-12">
	        <div class="card-3 card" id="system_call">

	            <div class="trend_indicator">
	                <div class="trend_arrow"></div>
	                <span></span>
	            </div>
	            <h1 class="title">{{__('widgets.system_calls')}} %</h1>
	            <h4 class="data total mt-3"></h4>

	        </div><!-- end card -->
	    </div><!-- end column -->

	    <div class="col-sm-3 col-xs-12">
	        <div class="card-3 card" id="total_minutes">

	            <div class="trend_indicator">
	                <div class="trend_arrow"></div>
	                <span></span>
	            </div>
	            <h1 class="title">{{__('widgets.total_minutes')}}</h1>
	            <h4 class="data total mt-3"></h4>

	        </div><!-- end card -->
	    </div><!-- end column -->

	    <div class="col-sm-3 col-xs-12">
	        <div class="card-3 card" id="total_calls">

	            <div class="trend_indicator">
	                <div class="trend_arrow"></div>
	                <span></span>
	            </div>
	            <h1 class="title">{{__('widgets.total_calls')}}</h1>
	            <h4 class="data total mt-3"></h4>

	        </div><!-- end card -->
	    </div><!-- end column -->
	</div>

	<div class="row">
		<div class="col-sm-3">
		   {{--  <div class="card-3 card blue flipping_card" id="callstatus_by_minutes">
		    	<h1 class="title">{{__('widgets.callstatus_by_minutes')}}</h1>
		    	<div class="front p20 mbp35">
		       		<canvas id="callstatus_by_minutes_graph"></canvas>
		     	</div>
		    </div> --}}<!-- end card -->

		    <div class="card flipping_card card-3b">
		        <div class="front ">
		            <div class="card_table">
		                <h1 class="title">{{__('widgets.callstatus_by_minutes')}}</h1>
		                <div class="flip_card_btn"></div>
		                <table class="table table-condensed table-striped" id="call_status_table">
		                    <thead>
		                        <tr>
		                            <th>{{__('widgets.call_status')}}</th>
		                            <th>{{__('widgets.minutes')}}</th>
		                            <th>{{__('widgets.count')}}</th>
		                        </tr>
		                    </thead>
		                    <tbody></tbody>
		                </table>
		            </div>
		        </div>
		        <div class="back">
		            <div class="flip_card_btn"></div>
		            <h1 class="title">{{__('widgets.callstatus_by_minutes')}}</h1>
		            <div class="inbound inandout mb0">
		                <canvas id="callstatus_by_minutes_graph"></canvas>
		            </div>
		        </div>
		    </div><!-- end card -->
		</div><!-- end column -->

		<div class="col-sm-9">
		    <div class="card-3 card blue" id="calls_minutes_per_day">
		        <h1 class="title">{{__('widgets.calls_minutes_per_day')}}</h1>
		        <div {{-- style="height: 280px" --}}>
		        	<canvas id="calls_minutes_per_day_graph"></canvas>
		        </div>
		    </div><!-- end card -->
		</div><!-- end column -->
	</div>

	<div class="row">
		<div class="col-sm-12">
			<div class="card card-3b mbo" style="height: 330px">
                <div class="card_table">
                    <h1 class="title">{{__('widgets.calls_by_campaign')}}</h1>
                    <table class="table table-condensed table-striped" id="calls_by_campaign">
                        <thead>
                            <tr>
                                <th>{{__('widgets.campaign')}}</th>
                                <th>{{__('widgets.total_calls')}}</th>
                                <th>{{__('widgets.duration')}}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
		</div><!-- end column -->
	</div>
</div>

