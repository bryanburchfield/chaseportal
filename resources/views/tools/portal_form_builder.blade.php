@extends('layouts.master')
@section('title', __('widgets.admin'))

@section('content')
<div class="preloader"></div>
<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
				
				<h1>Portal Form Builder</h1>			

		</div>
	</div>
	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')
@endsection