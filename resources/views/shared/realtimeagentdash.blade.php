@php
    $statuses = ['talking','wrapping','waiting','manual','paused'];
@endphp

<div class="row mt50">

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="total_calls_que">
            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('tools.total_calls_in_que')}}</h1>
            <h4 class="data total mt30 bg_rounded">0</h4>
        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="longest_hold_time">
            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('tools.longest_hold_in_que')}}</h1>
            <h4 class="data total mt30">00:00:00</h4>
        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="total_calls">
            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('tools.total_calls')}}</h1>
            <h4 class="data total mt30 bg_rounded">0</h4>
        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="total_sales">
            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('tools.total_sales')}}</h1>
            <h4 class="data total mt30 bg_rounded">0</h4>
        </div><!-- end card -->
    </div><!-- end column -->
</div>

<div class="row mt50">

    @foreach ($statuses as $status)
        @if($loop->first || $loop->index == 3)
            <div class="col-sm-{{$loop->first ? '7 five-three': '5 five-two'}} p0">
        @endif
        <div class="col-sm-{{$loop->index < 3 ? '4': '6'}} rep_status {{$status}}">
            <h2 class="mb0">{{__('general.' . $status)}}</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data']['statuses'][$status])}}</div>
                <p>{{__('general.agents')}}</p>
            </div>

            <ul id="{{$status}}" class="{{$status}} list-group cb">
                @foreach($data['data']['statuses'][$status] as $record)
                    <li class="list-group-item">
                        <span class="call_type">
                            @php
                                $has_icon='';
                                if($record['StatusCode'] == 5){
                                    echo '<i class="fa fa-sign-in-alt"></i>';
                                    $has_icon='has_icon';
                                }elseif ($record['StatusCode'] == 3 || $record['StatusCode'] == 4) {
                                    echo '<i class="fa fa-sign-out-alt"></i>';
                                    $has_icon='has_icon';
                                }
                            @endphp
                        </span>
                        <div class="agent_call_details {{$has_icon}}">
                            <p data-checksum="{{$record['checksum']}}" class="rep_name mb0">
                                {{$record['Login']}} <span class="timer">{{$record['TimeInStatus']}}</span></p>
                            <p class="campaign">{{$record['Campaign']}}</p>
                            <p class="break_code">{{$record['BreakCode']}}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @if($loop->index % 2 == 0 && $loop->index !=0)
            </div>
        @endif
    @endforeach

</div>

