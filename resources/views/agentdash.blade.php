@extends('layouts.dash')

@section('title', 'Agent Dashboard')

@section('content')
    
<div class="preloader"></div>
<input type="hidden" value="{{ $campaign }}" id="campaign" name="campaign">
<input type="hidden" value="{{ $datefilter }}" id="datefilter" name="datefilter">
<input type="hidden" value="{{ $inorout }}" id="inorout" name="inorout">

<?php
	dd(Session::get('userId'));
?>
@includeWhen(!$isApi, 'shared.navbar')

<div class="container-fluid bg">
    <div class="container mt50">
        @include('shared.filters')
			<div class="row">
                <div class="col-sm-3 col-xs-6">
                    <div class="card-3 card blue" id="total_outbound">
                        <h1 class="title">Total Outbound</h1>
                        <h4 class="data count total"></h4>
                    </div><!-- end card -->
                </div><!-- end column -->

                <div class="col-sm-3 col-xs-6">
                    <div class="card-3 card orange" id="total_inbound">
                        <h1 class="title">Total Inbound</h1>
                        <h4 class="data count total"></h4>
                    </div><!-- end card -->
                </div><!-- end column -->

                <div class="col-sm-3 col-xs-6">
                    <div class="card-3 card total_talktime_card green">
                        <h1 class="title">Total Talk Time</h1>
                        <h4 class="data" id="total_talktime"></h4>
                    </div><!-- end card -->
                </div><!-- end column -->

                <div class="col-sm-3 col-xs-6">
                    <div class="card-3 card avg_handle_time_card yellow">
                        <h1 class="title">Avg Handle Time</h1>
                        <h4 class="data" id="avg_handle_time"></h4>
                    </div><!-- end card -->
                </div><!-- end column -->
            </div>

            <div class="row bdrless_card">
                <div class="col-sm-6 pl0">
                    <div class="bdrcard">
                        <div class="col-sm-4 pr0">
                            <table class="table table-condensed ">
                                <tr class="bdrtop_none">
                                    <th>Call Types</th>
                                    <th class="tar"># Call Status</th>
                                </tr>

                                <tr>
                                    <td>Inbound</td>
                                    <td class="tar inbound_total"></td>
                                </tr>

                                <tr>
                                    <td>Manual</td>
                                    <td class="tar manual_total"></td>
                                </tr>

                                <tr>
                                    <td>Outbound</td>
                                    <td class="tar outbound_total"></td>
                                </tr>

                                <tr>
                                    <td>Total</td>
                                    <td class="tar total_calls"></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-sm-8 pl0">
                            <div class="card-6 card">
                                <div class="inbound inandout" style="height:240px">
                                    <canvas id="call_volume"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 pr0">
                    <div class="bdrcard">
                        <div class="col-sm-4 pr0">
                            <table class="table table-condensed ">
                                <tr class="bdrtop_none">
                                    <th>Call</th>
                                    <th class="tar">Talk Time</th>
                                </tr>

                                <tr>
                                    <td>Call</td>
                                    <td class="tar call_total"></td>
                                </tr>

                                <tr>
                                    <td>Paused</td>
                                    <td class="tar paused_total"></td>
                                </tr>

                                <tr>
                                    <td>Waiting</td>
                                    <td class="tar waiting_total"></td>
                                </tr>

                                <tr>
                                    <td>Wrap Up</td>
                                    <td class="tar wrapup_total"></td>
                                </tr>

                                <tr>
                                    <td>Total</td>
                                    <td class="tar total_total"></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-sm-8 pl0">
                            <div class="card-6 card">
                                <div class="inandout" style="height:240px">
                                    <canvas id="rep_performance"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-9">
                    <div class="card" style="height:280px">
                        <canvas id="call_status_count"></canvas>
                    </div>
                </div>

                <div class="col-sm-3 ">
                    <div class="card blue total_calls_card total_conversions">
                        <h1 class="title">Total Conversions</h1>
                        <!-- three dot menu -->
                        <div class="card_dropdown mv_left total_conversions">
                            <!-- three dots -->
                            <ul class="card_dropbtn icons btn-right showLeft">
                                <li></li>
                                <li></li>
                                <li></li>
                            </ul>
                            <!-- menu -->
                            <div id="card_dropdown" class="card_dropdown-content service_level_time">
                                <h3>Dispostion Type</h3>
                                <a href="Sale">Sale</a>
                                <a href="Appt">Appt</a>
                                <a href="Lead">Lead</a>
                            </div>
                        </div>
                        <h2 class="total_calls cnt"></h2>
                    </div>
                </div>
            </div>
	</div>
</div>

@include('shared.datepicker')

@endsection