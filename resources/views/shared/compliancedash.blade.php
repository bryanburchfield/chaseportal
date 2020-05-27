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
    					<th>Rep</th>
    					<th>Allowed Paused Time</th>
    					<th>PausedTime</th>
    					<th>% Worked</th>
    					<th>Total Time Worked</th>
    					<th>Worked Time</th>
    				</tr>
    			</thead>
    			<tbody></tbody>
    		</table>
    	</div>
    </div>

</div>