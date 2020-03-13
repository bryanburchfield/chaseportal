@extends('layouts.master')
@section('title', __('general.notifications'))

@section('content')
<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.navbar')
		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt20">
			    <div class="row">

					<div class="col-sm-12">
						<h2>{{$feature_message->title}} <a href="{{ URL::previous() }}" class="btn_flt_rgt btn btn-secondary"><i class="fas fa-arrow-circle-left"></i> Go Back</a></h2>
					</div>

					<div class="col-sm-6 notification_msg">
						{!! $feature_message->mark_down !!}
					</div>
			    </div>
			</div>
		</div>
	</div>

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@endsection