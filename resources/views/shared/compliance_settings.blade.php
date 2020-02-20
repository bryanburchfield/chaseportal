@extends('layouts.master')
@section('title', __('widgets.settings'))

@section('content')
<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">

		@include('shared.navbar')
		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt20">
			    <div class="row">
			    	@php
					    $controller = new App\Http\Controllers\ComplianceDashController();
					@endphp
				    <p>
				    <a href="{{ action('MasterDashController@complianceDashboard') }}">Back to Dashboard</a>
				    </p>

				    <div class="col-sm-6 card">
			    	    <form action="#" method="post" class="form">
			    	    	<label>Code</label>
			    	    	<div class="form-group">
			    	    		<select name="" id="" class="form-control">
			    	    			<option value="">Select One</option>
			    	    			@foreach ($controller->getPauseCodes() as $code)
			    						<option value="{{ $code }}">{{ $code }}</option>
			    	    			@endforeach
			    	    		</select>
			    	    	</div>

			    	    	<div class="form-group">
			    	    		<label>Minutes Per Day</label>
			    	    		<input type="text" class="form-control mins_per_day" name="mins_per_day">
			    	    	</div>

			    	    	<div class="form-group">
			    	    		<label>Times Per Day</label>
			    	    		<input type="text" class="form-control times_per_day" name="times_per_day">
			    	    	</div>

			    	    	<input type="submit" class="btn btn-primary" value="Do Something">
			    	    </form>
				    </div>
				</div>
			</div>
		</div>
	</div>
@include('shared.reportmodal')

@endsection