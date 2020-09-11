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
            <h4 class="data total mt20 mb20 bg_rounded">0</h4>
        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="longest_hold_time">
            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('tools.longest_hold_in_que')}}</h1>
            <h4 class="data total mt20 mb20">00:00:00</h4>
        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="total_calls">
            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('tools.total_calls')}}</h1>
            <h4 class="data total mt20 mb20 bg_rounded">0</h4>
        </div><!-- end card -->
    </div><!-- end column -->

    <div class="col-sm-3 col-xs-12">
        <div class="card-3 card" id="total_sales">
            <div class="trend_indicator">
                <div class="trend_arrow"></div>
                <span></span>
            </div>
            <h1 class="title">{{__('tools.total_sales')}}</h1>
            <h4 class="data total mt20 mb20 bg_rounded">0</h4>
        </div><!-- end card -->
    </div><!-- end column -->
</div>

<div class="row mt30">

    @foreach ($statuses as $status)
        @if($loop->first || $loop->index == 3)
            <div class="col-md-{{$loop->first ? '7 five-three': '5 five-two'}} p0">
        @endif
        <div class="col-md-{{$loop->index < 3 ? '4': '6'}} rep_status {{$status}}">
            <h2 class="mb0">{{__('general.' . $status)}}</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data']['statuses'][$status])}}</div>
                <p>{{__('general.agents')}}</p>
            </div>

            <ul id="{{$status}}" class="{{$status}} list-group cb">
                @foreach($data['data']['statuses'][$status] as $record)
                    <li class="list-group-item" {{$status == 'talking' ? 'data-toggle=modal data-target=#leadInspectionModal' : ''}}>
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

<!-- Lead Inspection Modal -->
<div class="modal fade" id="leadInspectionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.lead_details')}}</h4>
            </div>
            <div class="modal-body">
                <p class="lead_dets_leadid fz15 mb10"><b>Lead ID:</b><span></span></p>
                <p class="lead_dets_phone fz15"><b>Phone Number:</b> <span></span></p>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('general.cancel')}}</button>
        </div>
    </div>
    </div>
</div>

