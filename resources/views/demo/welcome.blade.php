@extends('layouts.dash')

@section('title', 'Chase Data Login')

@section('content')

	@include('shared.defaultHeader')

	<div class="container-fluid hero_bg">
        <div class="container">
            <div class="row">
            	<div class="col-md-8 col-md-offset-2">
            		<div class="form-holder welcome_box">
						<h1 class="mb20">Welcome to the Chase Data Portal! </h1>
					    <div class="alert alert-info demo_expiration">Your demo account will expire {{ $user->expires_in }}.</div>

					    <div class="btn_holder">
					    	<a class="btn btn-primary btn-lg" href="/">Go to Dashboards</a>
					    	<a class="btn btn-default btn-lg" target="_blank" href="https://demos.chasedatacorp.com/questionnaire/quote">Get a Quote</a>
					    </div>
					</div>
				</div>
            </div>
        </div>
    </div>

@endsection()