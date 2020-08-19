@php
    $statuses = ['talking','wrapping','waiting','manual','paused'];
@endphp

<div class="row">

    @foreach ($statuses as $status)
        @if($loop->first || $loop->index == 3)
            <div class="col-sm-{{$loop->first ? '7 five-three': '5 five-two'}} p0">
        @endif
        <div class="col-sm-{{$loop->index < 3 ? '4': '6'}} rep_status {{$status}}">
            <h2 class="mb0">{{__('general.' . $status)}}</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data'][$status])}}</div>
                <p>{{__('general.agents')}}</p>
            </div>

            <ul id="{{$status}}" class="{{$status}} list-group cb">
                @foreach($data['data'][$status] as $record)
                    <li class="list-group-item">
                        <p data-checksum="{{$record['checksum']}}" class="rep_name mb0">{{$record['Login']}} <span class="timer">{{$record['TimeInStatus']}}</span></p>
                        <p class="campaign">{{$record['Campaign']}}</p>
                    </li>
                @endforeach
            </ul>
        </div>

        @if($loop->index % 2 == 0 && $loop->index !=0)
            </div>
        @endif
    @endforeach

</div>

<div class="col-sm-offset-5 col-sm-2 mb20"><div id="txt" class="timer"></div></div>

