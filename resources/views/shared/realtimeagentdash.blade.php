    <div class="row">
        <div class="col-sm-2 rep_status talking">
            <h2>Talking</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data']['talking'])}}</div>
                <p>Agents</p>
            </div>

            @foreach($data['data']['talking'] as $talking)
                <p class="rep_name mb0">{{$talking['Login']}}</p>
                <p class="campaign">{{$talking['Campaign']}}</p>
            @endforeach
        </div>

        <div class="col-sm-2 rep_status wrapping">
            <h2>Wrapping Up</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data']['wrapping'])}}</div>
                <p>Agents</p>
            </div>

            @foreach($data['data']['wrapping'] as $wrapping)
                <p class="rep_name mb0">{{$wrapping['Login']}}</p>
                <p class="campaign">{{$wrapping['Campaign']}}</p>
            @endforeach
        </div>

        <div class="col-sm-2 rep_status waiting">
            <h2>Waiting</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data']['waiting'])}}</div>
                <p>Agents</p>
            </div>

            @foreach($data['data']['waiting'] as $waiting)
                <p class="rep_name mb0">{{$waiting['Login']}}</p>
                <p class="campaign">{{$waiting['Campaign']}}</p>
            @endforeach
        </div>

        <div class="col-sm-2 rep_status paused">
            <h2>Paused</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data']['paused'])}}</div>
                <p>Agents</p>
            </div>

            @foreach($data['data']['paused'] as $paused)
                <p class="rep_name mb0">{{$paused['Login']}}</p>
                <p class="campaign">{{$paused['Campaign']}}</p>
            @endforeach
        </div>

        <div class="col-sm-2 rep_status manual">
            <h2>Manual Calls</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data']['manual'])}}</div>
                <p>Agents</p>
            </div>

            @foreach($data['data']['manual'] as $manual)
                <p class="rep_name mb0">{{$manual['Login']}}</p>
                <p class="campaign">{{$manual['Campaign']}}</p>
            @endforeach
        </div>

        <div class="col-sm-2 rep_status break">
            <h2>Break</h2>
            <div class="num_agents">
                <div class="inner">{{count($data['data']['break'])}}</div>
                <p>Agents</p>
            </div>

            @foreach($data['data']['break'] as $break)
                <p class="rep_name mb0">{{$break['Login']}}</p>
                <p class="campaign">{{$break['Campaign']}}</p>
            @endforeach
        </div>
    </div>

    <div class="col-sm-offset-5 col-sm-2 mb20"><div id="txt" class="timer"></div></div>
    <div class="responsive-table">
