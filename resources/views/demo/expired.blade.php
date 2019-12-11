@extends('layouts.dash')

@section('title', 'Chase Data Login')

@section('content')

	@include('shared.defaultHeader')

	<div class="container-fluid hero_bg">
        <div class="container">
            <div class="row">
            	<div class="col-sm-8 col-sm-offset-2">
            		<div class="form-holder welcome_box expired">
						<div class="alert alert-danger">Sorry, your demo account expired {{ $user->expires_in }}.</div>
					</div>
				</div>
            </div>
        </div>
    </div>

@endsection()
