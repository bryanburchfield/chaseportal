@php
    $statuses = ['talking','wrapping','waiting','manual','paused'];
@endphp

<div class="row">

    @foreach ($statuses as $status)
        <div class="col-sm-2 rep_status {{$status}}">
            <h2 class="mb0">{{__('general.' . $status)}}</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data'][$status])}}</div>
                <p>{{__('general.agents')}}</p>
            </div>

            <ul id="{{$status}}" class="{{$status}} list-group cb">
                @foreach($data['data'][$status] as $record)
                    <li class="list-group-item">
                        <p data-checksum="{{$record['checksum']}}" class="rep_name mb0">{{$record['Login']}}</p>
                        <p class="campaign">{{$record['Campaign']}}</p>
                        <p>{{$record['TimeInStatus']}}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach

</div>

<div class="col-sm-offset-5 col-sm-2 mb20"><div id="txt" class="timer"></div></div>

