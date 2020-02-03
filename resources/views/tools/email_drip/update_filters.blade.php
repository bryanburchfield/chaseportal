@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')
<?php
	dd($email_drip_campaign);
?>
<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">
			    	<div class="col-sm-12">
			    		
			    	</div>
			    </div>
			</div>
		</div>
	</div>
</div>

@include('shared.reportmodal')