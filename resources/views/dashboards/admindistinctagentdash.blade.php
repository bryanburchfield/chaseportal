
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
    	<div class="col-sm-3 pl0 nopadright">
    	    <div class="col-sm-12 nopad">
    	    	<div class="card-3 card" id="distinct_agent_count">
    	    	    <div class="trend_indicator">
    	    	        <div class="trend_arrow"></div>
    	    	        <span></span>
    	    	    </div>
    	    	    <h1 class="title">{{__('widgets.distinct_agent_count')}} </h1>
    	    	    <h4 class="data total mt30"></h4>
    	    	</div><!-- end card -->
    	    </div><!-- end column -->

    	    <div class="col-sm-12 nopad">
    	        <div class="card-3 card" id="avg_reps">
    	            <div class="trend_indicator">
    	                <div class="trend_arrow"></div>
    	                <span></span>
    	            </div>
    	            <h1 class="title">{{__('widgets.avg_reps')}} </h1>
    	            <h4 class="data total mt30"></h4>
    	        </div><!-- end card -->
    	    </div><!-- end column -->

        	<div class="col-sm-12 nopad">
    		    <div class="card-3 card blue" id="distinct_reps_per_camp">
    		        <h1 class="title">{{__('widgets.distinct_reps_per_camp')}}</h1>
    		        <canvas id="distinct_reps_per_camp_graph"></canvas>
    		    </div><!-- end card -->
    		</div><!-- end column -->
    	</div><!-- end column -->

    	<div class="col-sm-9 pr0 nopadleft nopadright">
		    <div class="col-sm-12 nopad">
			    <div class="card card-3b mbo" style="height: 300px">
	                <div class="card_table">
	                    <h1 class="title">{{__('widgets.actions_timestamps')}}</h1>
	                    <table class="table table-condensed table-striped" id="actions">
	                        <thead>
	                            <tr>
	                                <th>{{__('widgets.date')}}</th>
	                                <th>{{__('widgets.rep')}}</th>
	                                <th>{{__('widgets.actions')}}</th>
	                            </tr>
	                        </thead>
	                        <tbody></tbody>
	                    </table>
	                </div>
	            </div>
		    </div><!-- end column -->

		    <div class="col-sm-12 nopad">
		    	<div class="card-3 card blue" id="logins_per_day">
		    	    <h1 class="title">{{__('widgets.distinct_logins_per_day')}}</h1>
		    	    <div style="height: 300px">
		    	    	<canvas id="logins_per_day_graph"></canvas>
		    	    </div>
		    	</div><!-- end card -->
		    </div><!-- end column -->
		</div><!-- end column -->
	</div>
</div>

