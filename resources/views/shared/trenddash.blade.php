<div class="row">
	<div class="col-sm-6 ">
		<div class="card card-6">
			<div class="call_volume_details flt_lft">
				<h1 class="title">{{__('widgets.call_volume')}}</h1>
				<p class="total mb_hide"></p>
			</div>

			<div class="btn-group btn-group-sm flt_rgt callvolume_inorout" role="group" aria-label="...">
				<button data-type="inbound" type="button" class="btn btn-primary">{{__('widgets.inbound')}}</button>
				<button data-type="outbound" type="button" class="btn btn-default">{{__('widgets.outbound')}}</button>
			</div>

			<div class="inbound inandout cb mt60">
				<canvas id="call_volume_inbound"></canvas>
			</div>

			<div class="outbound inandout cb mt60">
				<canvas id="call_volume_outbound"></canvas>
			</div>
		</div>
	</div>

	<div class="col-sm-6 ">
		<div class="card card-6">
			<h1 class="title mb0">{{__('widgets.call_details')}}</h1>
			<h2 class="avg_tt flt_rgt mb_hide"></h2>
			
			<div class="inbound inandout cb mt60">
				<canvas id="call_details"></canvas>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-6 ">
		<div class="card card-6">
			<h1 class="title mb0">{{__('widgets.avg_handle_time')}}</h1>
			<h2 class="avg_ht flt_rgt mb_hide"></h2>
			<p class="descrip mt10 top14 mb_hide">{{__('widgets.avg_handle_descr')}}</p><br>

			<div class="btn-group btn-group-sm " role="group" aria-label="..."></div>

			<div class="inbound inandout cb mt60" style="height: 300px">
				<canvas id="avg_handle_time"></canvas>
			</div>
		</div>
	</div>

	<div class="col-sm-6">
		<div class="card card-6">
			<h1 class="title mb0">{{__('widgets.service_level')}}</h1>
			<!-- three dot menu -->
			<div class="card_dropdown">
				<!-- three dots -->
				<ul class="card_dropbtn icons btn-right showLeft">
					<li></li>
					<li></li>
					<li></li>
				</ul>
				<!-- menu -->
				<div id="card_dropdown" class="card_dropdown-content service_level_time">
					<h3>{{__('widgets.change_answered_time')}}</h3>
					<a href="20">20 {{__('widgets.seconds')}}</a>
					<a href="30">30 {{__('widgets.seconds')}}</a>
					<a href="40">40 {{__('widgets.seconds')}}</a>
					<a href="50">50 {{__('widgets.seconds')}}</a>
					<a href="60">60 {{__('widgets.seconds')}}</a>
				</div>
			</div>
			<p class="descrip top14 mt10 mb_hide">{{__('widgets.handled_total')}} < <span class="answer_secs">20</span>
					{{__('widgets.sec_holdtime')}}</p><br>
			<div class="btn-group btn-group-sm " role="group" aria-label="..."></div>
			<div class="inbound inandout cb mt60" style="height: 300px">
				<canvas id="service_level"></canvas>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12 w99">
		<div class="card card-12">
			<h1 class="title mb0">{{__('widgets.agent_call_times')}}</h1><br>
			<div class="avgs_rgt flt_rgt">
				<h2 class="avg_cc flt_rgt mb_hide"></h2><br>
				<h2 class="avg_ct flt_rgt mb_hide"></h2>
			</div>
			<!-- <div class="btn-group btn-group-sm callvolume_inorout" role="group" aria-label="...">
	            </div> -->
			<div class="inbound inandout cb" style="height: 300px">
				<canvas id="rep_talktime"></canvas>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12 w99">
		<div class="card card-12">
			<h1 class="title">{{__('widgets.max_hold_time')}}</h1>
			<div class="inbound inandout cb" style="height:300px">
				<canvas id="max_hold_time"></canvas>
			</div>
		</div>
	</div>
</div>