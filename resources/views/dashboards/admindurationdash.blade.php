
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
	            <h4 class="data total mt30"></h4>

	        </div><!-- end card -->
	    </div><!-- end column -->

	    <div class="col-sm-3 col-xs-12">
	        <div class="card-3 card" id="system_call">

	            <div class="trend_indicator">
	                <div class="trend_arrow"></div>
	                <span></span>
	            </div>
	            <h1 class="title">{{__('widgets.system_calls')}}%</h1>
	            <h4 class="data total mt30"></h4>

	        </div><!-- end card -->
	    </div><!-- end column -->

	    <div class="col-sm-3 col-xs-12">
	        <div class="card-3 card" id="total_minutes">

	            <div class="trend_indicator">
	                <div class="trend_arrow"></div>
	                <span></span>
	            </div>
	            <h1 class="title">{{__('widgets.total_minutes')}}</h1>
	            <h4 class="data total mt30"></h4>

	        </div><!-- end card -->
	    </div><!-- end column -->

	    <div class="col-sm-3 col-xs-12">
	        <div class="card-3 card" id="total_calls">

	            <div class="trend_indicator">
	                <div class="trend_arrow"></div>
	                <span></span>
	            </div>
	            <h1 class="title">{{__('widgets.total_calls')}}</h1>
	            <h4 class="data total mt30"></h4>

	        </div><!-- end card -->
	    </div><!-- end column -->
	</div>

	<div class="row">
		<div class="col-sm-3">
		    <div class="card-3 card blue" id="callstatus_by_minutes">
		        <h1 class="title">{{__('widgets.callstatus_by_minutes')}}</h1>
		        <canvas id="callstatus_by_minutes_graph"></canvas>
		    </div><!-- end card -->
		</div><!-- end column -->

		<div class="col-sm-9">
		    <div class="card card-3b mbo" style="height: 330px">
                <div class="card_table">
                    <h1 class="title">{{__('widgets.calls_by_campaign')}}</h1>
                    <table class="table table-condensed table-striped" id="calls_by_campaign">
                        <thead>
                            <tr>
                                <th>{{__('widgets.campaign')}}</th>
                                <th>{{__('widgets.total_calls')}}</th>
                                <th>HH:MM:SS</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
		</div><!-- end column -->
	</div>

	<div class="row">
		<div class="col-sm-12">
		    <div class="card-3 card blue" id="calls_minutes_per_day">
		        <h1 class="title">{{__('widgets.calls_minutes_per_day')}}</h1>
		        <div style="height: 300px">
		        	<canvas id="calls_minutes_per_day_graph"></canvas>
		        </div>
		    </div><!-- end card -->
		</div><!-- end column -->
	</div>
</div>

