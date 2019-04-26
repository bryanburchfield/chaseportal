@extends('layouts.master')

@section('title', 'Master Dashboard')

@section('content')

<div class="preloader"></div>
<input type="hidden" value="{{ $campaign }}" id="campaign" name="campaign">
<input type="hidden" value="{{ $datefilter }}" id="datefilter" name="datefilter">
<input type="hidden" value="{{ $inorout }}" id="inorout" name="inorout">

<div class="wrapper">
    <nav id="sidebar" class="active">
        <div class="sidebar-header">
            <h3><img class="img-responsive" src="/img/chase_logo_blue.png" alt=""></h3>
            <strong><img src="/img/ChaseData-Transparent-Large2.png" class="img-responsive mb_logo"></strong>
        </div>

        <ul class="list-unstyled components">
            <li {!! $page['menuitem'] == 'admindash' ? 'class="active"' : ''!!}><a class="dash" href="admindash"><i class="fas fa-user-shield"></i>Admin Dashboard</a></li>
            <li {!! $page['menuitem'] == 'trenddash' ? 'class="active"' : ''!!}><a class="dash" href="trenddash"><i class="fas fa-chart-area"></i>Trend Dashboard</a></li>
            <li {!! $page['menuitem'] == 'leaderdash' ? 'class="active"' : ''!!}><a class="dash" href="leaderdash"><i class="fas fa-trophy"></i>Leadboard</a></li>
            <li {!! $page['menuitem'] == 'kpidash' ? 'class="active"' : ''!!}><a class="dash" href="kpidash"><i class="fas fa-paper-plane"></i>KPIs</a></li>
            <li {!! $page['menuitem'] == 'reports' ? 'class="active"' : ''!!} data-toggle="modal" data-target="#reports_modal"><a href="#"><i class="fas fa-file-contract"></i>Reports</a></li>
            @can('accessAdmin')
                <li {!! $page['menuitem'] == 'admin' ? 'class="active"' : ''!!}><a href="{{ url('master/admin') }}"><i class="fas fa-user-cog"></i>Admin</a></li>
            @endcan
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
               <div class="col-xs-3 col-sm-4 brand">
                   <button type="button" id="sidebarCollapse" class="btn">
                       <i class="fas fa-align-left"></i>
                   </button>
                   
                   <img src="/img/chase_text_logo.png" alt="" class="img-responsive text_logo">
               </div>

                <div class="filters col-xs-9 col-sm-8">
                    <div class="input-group">

                        <div class="input-group-btn">

                            <a href="{{ route('logout') }}" class="logout btn btn-primary btn-sm" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">Log Out</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            @if($page['type'] =='dash')
                                @include('master.dashnav')
                            @elseif($page['type'] == 'kpi_page')
                                @include('master.kpinav')
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </nav>

        <div class="container-fluid bg dashboard p20">
        @include($dashbody)
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="reports_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Select a Report</h4>
            </div>
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
                       <label><input type="radio" name="report_option" class="report_option" value="agent_call_log">Agent Call Log</label>
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
                <button type="button" class="btn btn-primary view_report_btn">View Report</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('shared.datepicker')
@endsection