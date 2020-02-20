@extends('layouts.report')

@section('title', __('general.compliance_dashboard'))

@section('content')


<div class="row">
    <div class="col-sm-12">
        <p><a href="{{ action('MasterDashController@complianceDashboard') }}">Back to Dashboard</a></p>

        <div class="col-sm-12 card">
    	    <form action="#" method="post" class="form">
    	    	@csrf
    			@foreach ($pause_codes as $key => $rec)
    				<div class="form-group col-sm-4">
    					<label>{{ $rec['code'] }}</label>
					</div>

					<div class="form-group col-sm-4">
						<label>Minutes Per Day</label>
						<input type="text" class="form-control minutes_per_day" name="minutes_per_day" value="{{$rec['minutes_per_day']}}">
					</div>

					<div class="form-group col-sm-4">
						<label>Times Per Day</label>
						<input type="text" class="form-control times_per_day" name="times_per_day" value="{{$rec['times_per_day']}}">
					</div>
    			@endforeach

	    		<input type="submit" class="btn btn-primary" value="Do Something">
    	    </form>
	    </div>
    </div>
</div>

@endsection
