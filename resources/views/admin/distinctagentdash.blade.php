@extends('layouts.master')
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
					<div class="col-sm-12">
						<h2>Distinct Agent Dashboard</h2>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')