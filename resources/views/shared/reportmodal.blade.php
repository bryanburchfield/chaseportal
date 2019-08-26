<!-- Modal -->
<div class="modal fade" id="reports_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                {{-- <button type="button" class="btn btn-sm btn-primary dropdown-toggle btn_flgrgt" data-toggle="dropdown" onclick="window.location.href = '{{ url('dashboards/reportsettings') }}';">
                    <span><i class="fa fa-cog"></i> Report Settings</span>
                </button> --}}
                <h4 class="modal-title" id="myModalLabel">Select a Report</h4>
            </div>

            {!! Form::open(['method'=>'POST', 'action'=> 'MasterDashController@showReport' ]) !!}
            <div class="modal-body">
               <div class="col-sm-6 nopad">
                   <div class="radio">
                       <label><input type="radio" name="report_option" class="report_option" value="call_details">Call Details</label>
                   </div>
                   <div class="radio">
                       <label><input type="radio" name="report_option" class="report_option" value="agent_analysis">Agent Analysis</label>
                   </div>
                   <div class="radio">
                       <label><input type="radio" name="report_option" class="report_option" value="agent_summary">Agent Summary</label>
                   </div>
                   <div class="radio">
                       <label><input type="radio" name="report_option" class="report_option" value="agent_summary_campaign">Agent Summary By Camp</label>
                   </div>
                   <div class="radio">
                       <label><input type="radio" name="report_option" class="report_option" value="agent_summary_subcampaign">Agent Summary By Sub</label>
                   </div>
                   <br>
                   <div class="radio">
                       <label><input type="radio" name="report_option" class="report_option" value="agent_pause_time">Agent Pause Time</label>
                   </div>
                   <div class="radio">
                       <label><input type="radio" name="report_option" class="report_option" value="agent_activity">Agent Activity</label>
                   </div>
                   <div class="radio">
                       <label><input type="radio" name="report_option" class="report_option" value="agent_timesheet">Agent Time Sheet</label>
                   </div>
                   
                   <div class="radio">
                       <label><input type="radio" name="report_option" class="report_option" value="campaign_usage">Campaign Usage</label>
                   </div>
               </div>

                <div class="col-sm-6 nopad">
                    <div class="radio">
                        <label><input type="radio" name="report_option" class="report_option" value="inbound_summary">Inbound Summary</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="report_option" class="report_option" value="campaign_summary">Campaign Summary</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="report_option" class="report_option" value="subcampaign_summary">Subcampaign Summary</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="report_option" class="report_option" value="campaign_call_log">Campaign Call Log</label>
                    </div>
                    <br>
                    <div class="radio">
                        <label><input type="radio" name="report_option" class="report_option" value="shift_report">Shift Report</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="report_option" class="report_option" value="production_report">Production Report</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="report_option" class="report_option" value="production_report_subcampaign">Production By Sub</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="report_option" class="report_option" value="lead_inventory">Lead Inventory</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="report_option" class="report_option" value="lead_inventory_sub">Lead Inventory By Sub</label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                {{-- @csrf --}}
                {{-- <button type="button" class="btn btn-primary view_report_btn">View Report</button> --}}
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <a href="#" class="mb0 btn_flgrgt" onclick="window.location.href = '{{ url('dashboards/reportsettings') }}';">
                    <span><i class="fa fa-cog"></i> Report Settings</span>
                </a>
                {!! Form::submit('View Report',['class'=>'btn btn-primary mb0']) !!}
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>