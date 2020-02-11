@extends('layouts.dash')
@section('title', __('widgets.admin'))

@section('content')
<div class="preloader"></div>
<?php
	//dd($default_lead_fields);
?>
<div class="wrapper">

	@include('shared.admin_sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
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
				            <h1 class="title">{{__('widgets.system_call')}}%</h1>
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
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')