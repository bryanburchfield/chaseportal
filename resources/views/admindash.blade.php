@extends('layouts.dash')

@section('title', 'Admin Dashboard')

@section('content')
    
<div class="preloader"></div>
<input type="hidden" value="{{ $campaign }}" id="campaign" name="campaign">
<input type="hidden" value="{{ $datefilter }}" id="datefilter" name="datefilter">
<input type="hidden" value="{{ $inorout }}" id="inorout" name="inorout">

@includeWhen(!$isApi, 'shared.navbar')

<div class="container-fluid bg">
    <div class="container mt50">
        @include('shared.filters')

        <div class="row">
            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card" id="completed_calls">

                    <h1 class="title">Completed Calls</h1>
                    <h4 class="data count total"></h4>

                    <div class="divider"></div>

                    <div class="inbound">
                        <p class="data inbound"></p>
                        <p class="type">Inbound</p>
                    </div>

                    <div class="outbound">
                        <p class="data outbound"></p>
                        <p class="type">Outbound</p>
                    </div>
                </div><!-- end card -->
            </div><!-- end column -->

            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card" id="total_minutes">

                    <h1 class="title">Total Minutes</h1>
                    <h4 class="data count total"></h4>

                    <div class="divider"></div>

                    <div class="inbound">
                        <p class="data inbound"></p>
                        <p class="type">Inbound</p>
                    </div>

                    <div class="outbound">
                        <p class="data outbound"></p>
                        <p class="type">Outbound</p>
                    </div>
                </div><!-- end card -->
            </div><!-- end column -->

            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card avg_hold_time_card">
                    <h1 class="title">Average Hold Time</h1>
                    <h4 class="data" id="avg_hold_time"></h4>

                    <div class="divider"></div>

                    <div class="details tac">
                        <p class="data" id="total_hold_time"></p>
                        <p class="type">Total Hold Time</p>
                    </div>
                </div><!-- end card -->
            </div><!-- end column -->

            <div class="col-sm-3 col-xs-6">
                <div class="card-3 card abandon_calls_card">
                    <h1 class="title">Abandoned Calls</h1>
                    <h4 class="data count" id="abandon_calls"></h4>

                    <div class="divider"></div>

                    <div class="details tac">
                        <p class="data" id="abandon_rate"></p>
                        <p class="type">Abandon Rate</p>
                    </div>
                </div><!-- end card -->
            </div><!-- end column -->
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="card-6 card" style="height: 358px">
                    <h1 class="title">Call Volume</h1>
                    <div class="btn-group btn-group-sm callvolume_inorout" role="group" aria-label="...">
                        <button data-type="inbound" type="button" class="btn btn-primary">Inbound</button>
                        <button data-type="outbound" type="button" class="btn btn-default">Outbound</button>
                    </div>

                    <div class="inbound inandout">
                        <canvas id="call_volume_inbound"></canvas>
                    </div>

                    <div class="outbound inandout">
                        <canvas id="call_volume_outbound"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card-6 card" style="height: 358px">
                    <h1 class="title">Call Duration</h1>

                    <div class="inandout">
                        <canvas id="call_duration"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <div class="card">
                    <canvas id="agent_call_count"></canvas>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="card">
                    <p class="descrip">Handled/Total. Handled is answered with < 20 sec holdtime</p>
                    <canvas id="service_level"></canvas>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="card">
                    <canvas id="agent_calltime"></canvas>
                </div>
            </div>

            <div class="col-sm-3 card_table_prt">
                <div class="card card_table">
                    <h2 class="card_title">REP AVG HANDLE TIME</h2>
                    <table class="table table-condensed table-striped" id="rep_avg_handletime"></table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('shared.datepicker')

@endsection