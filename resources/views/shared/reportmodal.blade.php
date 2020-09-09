<!-- Modal -->
<div class="modal fade" id="reports_modal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="reportModalLabel">{{__('general.select_report')}}</h4>
            </div>

            {!! Form::open(['method'=>'POST', 'action'=> 'MasterDashController@showReport' ]) !!}
            <div class="modal-body">

                @php
                    $reports = [
                        'agent_activity' =>  __('reports.agent_activity'),
                        'agent_analysis' =>  __('reports.agent_analysis'),
                        'agent_pause_time' =>  __('reports.agent_pause_time'),
                        'agent_summary' =>  __('reports.agent_summary'),
                        'agent_summary_campaign' =>  __('reports.agent_summary_campaign'),
                        'agent_summary_subcampaign' =>  __('reports.agent_summary_subcampaign'),
                        'agent_timesheet' =>  __('reports.agent_timesheet'),
                        'caller_id' =>  __('reports.caller_id'),
                        'calls_per_hour' =>  __('reports.calls_per_hour'),
                        'campaign_call_log' => __('reports.campaign_call_log'),
                        'campaign_summary' =>  __('reports.campaign_summary'),
                        'campaign_usage' =>  __('reports.campaign_usage'),
                        'inbound_summary' =>  __('reports.inbound_summary'),
                        'lead_inventory' =>  __('reports.lead_inventory'),
                        'lead_inventory_sub' =>  __('reports.lead_inventory_sub'),
                        'missed_calls' =>   __('reports.missed_calls'),
                        'production_report' =>  __('reports.production_report'),
                        'production_report_subcampaign' =>  __('reports.production_report_subcampaign'),
                        'shift_report' =>  __('reports.shift_report'),
                        'subcampaign_summary' =>  __('reports.subcampaign_summary'),
                        'transfer_agent' =>  __('reports.transfer_agent'),
                    ];

                    if(Auth::User()->group_id == 224500) {
                        unset($reports['production_report']);
                        unset($reports['campaign_call_log']);
                        
                        $reports['bwr_omni'] = __('reports.bwr_omni');
                        $reports['bwr_production_report'] = __('reports.production_report');
                        $reports['bwr_campaign_call_log'] = __('reports.campaign_call_log');
                    }
                    
                    if(Auth::User()->group_id == 224802) {
                        unset($reports['agent_summary']);
                        unset($reports['subcampaign_summary']);
                        $reports['apn_agent_summary'] = __('reports.agent_summary');
                        $reports['apn_subcampaign_summary'] = __('reports.subcampaign_summary');
                    }

                    if(!Auth::User()->isDemo()) {
                        $reports['call_details'] = __('reports.call_details');
                    }

                    asort($reports);

                    // split the list in half
                    $half = ceil(count($reports) / 2);
                    $list1 = array_slice($reports, 0, $half);
                    $list2 = array_slice($reports, $half);
                @endphp

                <div class="col-sm-6 p0">
                    @foreach ($list1 as $key => $value)
                        <div class="radio">
                            <label><input type="radio" name="report_option" class="report_option" value="{{$key}}">{{$value}}</label>
                        </div>
                    @endforeach
                </div>
                <div class="col-sm-6 p0">
                    @foreach ($list2 as $key => $value)
                        <div class="radio">
                            <label><input type="radio" name="report_option" class="report_option" value="{{$key}}">{{$value}}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer">
                {{-- @csrf --}}
                {{-- <button type="button" class="btn btn-primary view_report_btn">View Report</button> --}}
                <button type="button" class="btn btn-default mr10 flt_lft" data-dismiss="modal">{{__('general.close')}}</button>
                <a href="#" class="mb0" onclick="window.location.href = '{{ url('dashboards/automatedreports') }}';">
                    <span><i class="fa fa-cog"></i> {{__('general.auto_reports')}}</span>
                </a>
                <a href="#" class="view_report_btn btn-primary btn flt_lft">{{__('general.view_report')}}</a>
            </div>
            {!! Form::close() !!}

        </div>
    </div>
</div>