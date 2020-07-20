@extends('layouts.minimal')
@section('title', __('general.optout'))

@section('content')
	
	<div class="container">
		<div class="row">
			<div class="col-sm-6 col-sm-offset-3 mt100">
				<div class="alert alert-info fz15 tac">{{__('general.unsubscribed')}}</div>
			</div>
		</div>
	</div>

@endsection