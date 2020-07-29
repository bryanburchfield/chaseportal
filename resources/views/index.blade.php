@extends('layouts.dash')

@section('title', 'Chase Data Portal')

@section('content')

<div class="wrapper">

    @include('shared.sidenav')

    <div id="content">
        @include('shared.navbar')

        <div class="container-fluid lp_bg">
            <div class="lp_grad">
                <div class="col-sm-12">
				    <img src="img/logo_white.png" alt="" class="img-responsive">
				    <p>Portal for tools and reporting on your call center needs</p>
                </div>
            </div>
        </div>
    </div>
	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@endsection