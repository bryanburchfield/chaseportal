
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
    	<div class="col-sm-3">
    	    <div class="col-sm-12 p0">
    	    	<div class="card-3 card" id="distinct_agent_count">
    	    	    <div class="trend_indicator">
    	    	        <div class="trend_arrow"></div>
    	    	        <span></span>
    	    	    </div>
    	    	    <h1 class="title">{{__('widgets.distinct_agent_count')}} </h1>
    	    	    <h4 class="data total mt-3 bg_rounded"></h4>
    	    	</div><!-- end card -->
    	    </div><!-- end column -->

    	    <div class="col-sm-12 p0">
    	        <div class="card-3 card" id="avg_reps">
    	            <div class="trend_indicator">
    	                <div class="trend_arrow"></div>
    	                <span></span>
    	            </div>
    	            <h1 class="title">{{__('widgets.avg_reps')}} </h1>
    	            <h4 class="data total mt-3"></h4>
    	        </div><!-- end card -->
    	    </div><!-- end column -->

        	<div class="col-sm-12 p0">
    		    <div class="card-3 card blue flipping_card" id="distinct_reps_per_camp">
    		        <h1 class="title">{{__('widgets.distinct_reps_per_camp')}}</h1>
                    <div class="front p20 mbp35">
    		          <canvas id="distinct_reps_per_camp_graph"></canvas>
                  </div>
    		    </div><!-- end card -->
    		</div><!-- end column -->
    	</div><!-- end column -->

    	<div class="col-sm-9 ">
		    <div class="col-sm-12 p0">
			    <div class="card-3 card blue" id="logins_per_day">
                    <h1 class="title">{{__('widgets.distinct_logins_per_day')}}</h1>
                    <!-- three dot menu -->
                    <div class="card_dropdown mv_left">
                        <!-- three dots -->
                        <ul class="card_dropbtn icons btn-left showLeft">
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>
                        <p class="login_date flt_rgt"></p>
                        <!-- menu -->
                        <div id="card_dropdown" class="card_dropdown-content logins_drilldown">
                            <h3>{{__('widgets.view_day_details')}}</h3>
                            <div class="options"></div>
                        </div>
                    </div>


                    <div style="height: 312px">
                        <canvas id="logins_per_day_graph"></canvas>
                    </div>
                </div><!-- end card -->
		    </div><!-- end column -->

		    <div class="col-sm-12 p0">
                <div class="card card-3b mbo" style="height: 345px">
                    <div class="card_table">
                        <h1 class="title">{{__('widgets.actions_timestamps')}}</h1>
                        <div class="table-responsive table-responsive-sm">
                            <table class="table table-condensed table-striped" id="actions">
                                <thead>
                                    <tr>
                                        <th>
                                            <span>{{__('general.date')}}</span>
                                            <a href="#" class="sort-by"> <span class="asc"></span><span class="desc"></span></a>
                                        </th>

                                        <th>
                                            <span>{{__('widgets.rep')}}</span>
                                            <a href="#" class="sort-by"> <span class="asc"></span><span class="desc"></span></a>
                                        </th>

                                        <th>
                                            <span>{{__('widgets.actions')}}</span>
                                            <a href="#" class="sort-by"> <span class="asc"></span><span class="desc"></span></a>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
		    </div><!-- end column -->
		</div><!-- end column -->
	</div>
</div>

