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
                    'call_details' => __('reports.call_details'),
                    'agent_analysis' =>  __('reports.agent_analysis'),
                    'agent_summary' =>  __('reports.agent_summary'),
                    'agent_summary_campaign' =>  __('reports.agent_summary_campaign'),
                    'agent_summary_subcampaign' =>  __('reports.agent_summary_subcampaign'),
                    'agent_pause_time' =>  __('reports.agent_pause_time'),
                    'agent_activity' =>  __('reports.agent_activity'),
                    'agent_timesheet' =>  __('reports.agent_timesheet'),
                    'campaign_usage' =>  __('reports.campaign_usage'),
                    'caller_id' =>  __('reports.caller_id'),
                    'inbound_summary' =>  __('reports.inbound_summary'),
                    'campaign_summary' =>  __('reports.campaign_summary'),
                    'subcampaign_summary' =>  __('reports.subcampaign_summary'),
                    'campaign_call_log' => __('reports.campaign_call_log'),
                    'missed_calls' =>   __('reports.missed_calls'),
                    'shift_report' =>  __('reports.shift_report'),
                    'production_report' =>  __('reports.production_report'),
                    'production_report_subcampaign' =>  __('reports.production_report_subcampaign'),
                    'lead_inventory' =>  __('reports.lead_inventory'),
                    'lead_inventory_sub' =>  __('reports.lead_inventory_sub')
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