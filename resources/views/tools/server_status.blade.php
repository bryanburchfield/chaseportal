@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50">
			    <div class="row">
			    	<div class="col-sm-12">
						<iframe style="width: 100%; height: 100%; min-height:1000px; position: absolute";" class="embed-responsive-item" src="{{ Auth::user()->dialer->status_url }}" allowfullscreen></iframe>
					</div>
				</div>
			</div>
		</div>
	</div>
	@include('shared.notifications_bar')
</div>


@endsection