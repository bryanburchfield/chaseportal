<!-- Modal -->
<div class="modal fade" id="reports_modal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="reportModalLabel">Select a Report</h4>
            </div>

            {!! Form::open(['method'=>'POST', 'action'=> 'MasterDashController@showReport' ]) !!}
            <div class="modal-body">

                @php

                $reports = [
                    'call_details' => 'Call Details',
                    'agent_analysis' => 'Agent Analysis',
                    'agent_summary' => 'Agent Summary',
                    'agent_summary_campaign' => 'Agent Summary By Camp',
                    'agent_summary_subcampaign' => 'Agent Summary By Sub',
                    'agent_pause_time' => 'Agent Pause Time',
                    'agent_activity' => 'Agent Activity',
                    'agent_timesheet' => 'Agent Time Sheet',
                    'campaign_usage' => 'Campaign Usage',
                    'caller_id' => 'Caller ID Tracking',
                    'inbound_summary' => 'Inbound Summary',
                    'campaign_summary' => 'Campaign Summary',
                    'subcampaign_summary' => 'Subcampaign Summary',
                    'campaign_call_log' => 'Campaign Call Log',
                    'missed_calls' =>  'Missed Calls',
                    'shift_report' => 'Shift Report',
                    'production_report' => 'Production Report',
                    'production_report_subcampaign' => 'Production By Sub',
                    'lead_inventory' => 'Lead Inventory',
                    'lead_inventory_sub' => 'Lead Inventory By Sub'
                ];

                asort($reports);

                $i=0;
                foreach($reports as $key => $value){
                    if(!$i){echo '<div class="col-sm-6 nopad">';}
                    if($i == count($reports) / 2 + 1){echo '<div class="col-sm-6 nopad">';}
                    echo '<div class="radio">';
                        echo '<label><input type="radio" name="report_option" class="report_option" value="'.$key.'">'.$value.'</label>';
                    echo '</div>';
                    if($i == count($reports) / 2 || $i == count($reports) -1){echo '</div>';}
                    $i++;
                }
                @endphp
            </div>

            <div class="modal-footer">
                {{-- @csrf --}}
                {{-- <button type="button" class="btn btn-primary view_report_btn">View Report</button> --}}
                <button type="button" class="btn btn-default mr10" data-dismiss="modal">Close</button>
                <a href="#" class="mb0 btn_flgrgt" onclick="window.location.href = '{{ url('dashboards/automatedreports') }}';">
                    <span><i class="fa fa-cog"></i> Automated Reports</span>
                </a>
                <a href="#" class="view_report_btn btn-primary btn">View Report</a>
            </div>
            {!! Form::close() !!}

        </div>
    </div>
</div>