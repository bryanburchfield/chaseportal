@if ($message = Session::get('flash'))
    <div class="alert alert-info alert-block">
        <button type="button" class="close" aria-label="Close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        <strong>{{ $message }}</strong>
    </div>
@endif

<div class="row">
    <div class="col-sm-12">
    	<a class="btn btn-default btn-primary" href="{{ action('ComplianceDashController@settingsIndex') }}">Go To Settings</a>

    	<div class="table-responsive">
    		<table class="table table-striped agent_compliance_table">
    			<thead>
    				<tr>
    					<th class="th_mw100">Rep</th>
    					<th>Worked Time</th>
    					<th>Paused Time</th>
    					<th>Allowed Paused Time</th>
    					<th>Total Time Worked</th>
    					<th>% Worked</th>
    				</tr>
    			</thead>
    			<tbody></tbody>
    		</table>
    	</div>
    </div>

    <div class="col-sm-3">
    	<div class="card card-3b">
	    	<h1 class="title">{{__('widgets.adherence')}}</h1>
	    	<canvas id="agent_worked_graph"></canvas>
    	</div>
    </div>

</div>