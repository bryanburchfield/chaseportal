@if ($message = Session::get('flash'))
    <div class="alert alert-info alert-block">
        <button type="button" class="close" aria-label="Close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        <strong>{{ $message }}</strong>
    </div>
@endif

<div class="row">
    <div class="col-sm-12">
    	<a class="btn btn-default btn-primary flt_rgt" href="{{ action('ComplianceDashController@settingsIndex') }}">{{__('widgets.go_to_settings')}}</a>

    	<div class="table-responsive">
    		<table class="table table-striped agent_compliance_table">
    			<thead>
    				<tr>
                        <th></th>
    					<th class="th_mw100">{{__('widgets.rep')}}</th>
    					<th>{{__('widgets.worked_time')}}</th>
    					<th>{{__('widgets.paused_time')}}</th>
    					<th>{{__('widgets.allowed_paused_time')}}</th>
    					<th>{{__('widgets.total_time_worked')}}</th>
    					<th>% {{__('widgets.worked')}}</th>
    				</tr>
    			</thead>
    			<tbody></tbody>
    		</table>
    	</div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 w99">
    	<div class="card card-12 mt30">
	    	<h1 class="title">{{__('widgets.adherence')}}</h1>
	    	<div class="inbound" style="height:350px;">
	    		<canvas id="agent_worked_graph"></canvas>
	    	</div>
    	</div>
    </div>
</div>

<!-- Agent Details Modal -->
<div class="modal fade " id="agentModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('widgets.agent_details')}}</h4>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped agent_details">
                        <thead>
                            <tr>
                                <th>{{__('general.date')}}</th>
                                <th>{{__('widgets.action')}}</th>
                                <th>{{__('widgets.details')}}</th>
                                <th>{{__('widgets.worked_time')}}</th>
                                <th>{{__('widgets.paused_time')}}</th>
                                <th>{{__('widgets.allowed_paused_time')}}</th>                                
                            </tr>
                        </thead>

                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>