@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<div class="preloader"></div>
<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">
			    	<div class="col-sm-12">
                        <h2 class="bbnone">{{__('tools.playbook_touches')}}</h2>

                        <div class="touch col-sm-2">
                            <a href="#"><i class="fas fa-fingerprint fa-3x"></i></a>
                            <h4 class="name">SMS</h4>
                        </div>

                        <div class="touch col-sm-2">
                            <a href="#"><i class="fas fa-fingerprint fa-3x"></i></a>
                            <h4 class="name">Email</h4>
                        </div>

                        <div class="touch col-sm-2">
                            <a href="#"><i class="fas fa-fingerprint fa-3x"></i></a>
                            <h4 class="name">SMS</h4>
                        </div>
			    	</div>
				</div>
			</div>
		</div>
	</div>

    @include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@endsection