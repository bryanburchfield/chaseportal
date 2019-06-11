@extends('layouts.dash')

@section('title', 'Leaderboard Dashboard')

@section('content')

<div class="preloader"></div>
<input type="hidden" value="{{ $campaign }}" id="campaign" name="campaign">
<input type="hidden" value="{{ $datefilter }}" id="datefilter" name="datefilter">
<input type="hidden" value="{{ $inorout }}" id="inorout" name="inorout">

@includeWhen(!$isApi, 'shared.navbar')

<div class="container-fluid bg">
    <div class="container mt50">
        @include('shared.filters')

        <div class="row leaderboard_main_row">

            <div class="col-md-3 col-sm-4 leader_table_div_colm card_table_prt">
                <div class="card plr0 leader_table_div card_table">
                    <h1 class="title mb0">Sales Leaderboard</h1>

                    <div class="table-responsive overflowauto">
                        <table class="table table-striped salesleaderboardtable">
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-9 col-sm-8 get_ldr_ht">
                <div class="card card-12">
                    <div class="call_volume_details">
                        <h1 class="title">Call Volume</h1><br>
                    </div>

                    <div class="inbound inandout" style="height: 300px">
                        <canvas id="call_volume"></canvas>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 pl0 match_height_4_gt">
                    <div class="card card-6">
                        <div class="inbound inandout">
                            <canvas id="calls_by_campaign"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 match_height_4_st">
                    <div class="total_calls_out">
                        <h2>Total Outbound Calls</h2>
                        <p class="total"></p>
                    </div>

                    <div class="total_calls_in">
                        <h2>Total Inbound Calls</h2>
                        <p class="total"></p>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 pr0 match_height_4_st">
                    <div class="card card-6">
                        <!-- <h1 class="title mb0">Agent Calls by Campaign</h1> -->

                        <div class="inbound inandout">
                            <canvas id="agent_calls_by_campaign"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@include('shared.datepicker')
@endsection