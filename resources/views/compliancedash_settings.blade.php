@extends('layouts.report')

@section('title', __('general.compliance_dashboard'))

@section('content')


<div class="row">
    <div class="col-sm-12">
        <p><a href="{{ action('MasterDashController@complianceDashboard') }}">Back to Dashboard</a></p>

        <div class="card p0">
        	<table class="table">
        		<thead>
        			<th>Code</th>
        			<th>Minutes Per Day</th>
        			<th># ofTimes Per Day</th>
        		</thead>

        		<tbody>
		    	    <form action="#" method="post" class="form compliance_settings">
		    	    	@csrf
		    			@foreach ($pause_codes as $key => $value)
		    				<tr>
			    				<td>
				    				<div class="form-group">
				    	    			<input type="text" class="form-control minutes_per_day" disabled name="minutes_per_day" value="{{ $value['code'] }}">
									</div>
			    				</td>

								<td>
									<div class="form-group">
										<input type="text" class="form-control minutes_per_day" name="minutes_per_day" value="{{$key['minutes_per_day']}}">
									</div>
								</td>

								<td>
									<div class="form-group">
										<input type="text" class="form-control times_per_day" name="times_per_day"  value="{{$key['times_per_day']}}">
									</div>
								</td>
							</tr>
		    			@endforeach

			    		<input type="submit" class="btn btn-primary" value="Do Something">
		    	    </form>
        		</tbody>
        	</table>
    	    
	    </div>
    </div>
</div>

@endsection
