@php
    // dd($data);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{csrf_token()}}" />

        <title>Laravel</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .timer{
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-2 rep_status talking">
                    <h2>Talking</h2>
                    <div class="num_agents">
                        <div class="inner">{{count($data['talking'])}}</div>
                        <p>Agents</p>
                    </div>

                   {{--  @foreach($data['talking'] as $talking)
                        <p class="rep_name mb0">{{$talking['Login']}}</p>
                        <p class="campaign">{{$talking['Campaign']}}</p>
                    @endforeach --}}
                </div>

                <div class="col-sm-2 rep_status wrapping">
                    <h2>Wrapping Up</h2>
                    <div class="num_agents">
                        <div class="inner">{{count($data['wrapping'])}}</div>
                        <p>Agents</p>
                    </div>

                    {{-- @foreach($data['wrapping'] as $wrapping)
                        <p class="rep_name mb0">{{$wrapping['Login']}}</p>
                        <p class="campaign">{{$wrapping['Campaign']}}</p>
                    @endforeach --}}
                </div>

                <div class="col-sm-2 rep_status waiting">
                    <h2>Waiting</h2>
                    <div class="num_agents">
                        <div class="inner">{{count($data['waiting'])}}</div>
                        <p>Agents</p>
                    </div>

                    @foreach($data['waiting'] as $waiting)
                        <p class="rep_name mb0">{{$waiting['Login']}}</p>
                        <p class="campaign">{{$waiting['Campaign']}}</p>
                    @endforeach
                </div>

                <div class="col-sm-2 rep_status paused">
                    <h2>Paused</h2>
                    <div class="num_agents">
                        <div class="inner">{{count($data['paused'])}}</div>
                        <p>Agents</p>
                    </div>

                    @foreach($data['paused'] as $paused)
                        <p class="rep_name mb0">{{$paused['Login']}}</p>
                        <p class="campaign">{{$paused['Campaign']}}</p>
                    @endforeach
                </div>

                <div class="col-sm-2 rep_status manual">
                    <h2>Manual Calls</h2>
                    <div class="num_agents">
                        <div class="inner">{{count($data['manual'])}}</div>
                        <p>Agents</p>
                    </div>

                    @foreach($data['manual'] as $manual)
                        <p class="rep_name mb0">{{$manual['Login']}}</p>
                        <p class="campaign">{{$manual['Campaign']}}</p>
                    @endforeach
                </div>

                <div class="col-sm-2 rep_status break">
                    <h2>Break</h2>
                    <div class="num_agents">
                        <div class="inner">{{count($data['break'])}}</div>
                        <p>Agents</p>
                    </div>

                    @foreach($data['break'] as $break)
                        <p class="rep_name mb0">{{$break['Login']}}</p>
                        <p class="campaign">{{$break['Campaign']}}</p>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-sm-offset-5 col-sm-2 mb20"><div id="txt" class="timer"></div></div>
        <div class="responsive-table">

        </div>
        <script src="{{asset('js/app.js')}}"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js" type="text/javascript"></script>
        <script>
            var ran=false,
                trs,
                timers=[],
                time = '12:05:14'
            ;

            Echo.channel('{{ $channel }}')
                .listen('NewMessage', (e) => {
                    console.log(typeof(e.message));

                    var result = Object.entries(e.message);
                    console.log(result);

                    for (var i=0; i < result.length; i++) {
                        var column = result[i][0];
                        var agents='';
                        for(j=0;j<result[i][1].length;j++){
                            agents += '<p class="rep_name mb0">'+result[i][1][j]['Login']+'</p><p class="campaign">'+result[i][1][j]['Campaign']+'</p>';
                        }
                        console.log('AGENTS: '+agents);
                        $('.rep_status.'+column).append(agents);
                    }


                    // $('.realtime_table tbody').empty();
                    // var start_time=0;
                    // console.log(e.message); // 506

                    // const status_types = Object.keys(e.message);
                    // console.log(status_types);

                    // function start_timer(start_time, row){
                    //     const zeroPad = (num, places) => String(num).padStart(places, '0');
                    //     start_time = parseInt(start_time);

                    //     var x = setInterval(function () {

                    //         start_time = start_time + 1;
                    //         var hours = Math.floor(start_time / 3600);
                    //         var minutes = Math.floor((start_time / 60) % 60);
                    //         var seconds = start_time % 60;

                    //         $('.realtime_table tbody tr:eq('+row+')').find('td.instatus_timer').empty();
                    //         $('.realtime_table tbody tr:eq('+row+')').find('td.instatus_timer').text(zeroPad(hours, 2) + ":" + zeroPad(minutes, 2) + ":" + zeroPad(seconds, 2));
                    //     }, 1000);
                    // }

                    // trs='';

                    // for(var i=0;i<e.message.results.length;i++){

                    //     if(!ran){
                    //         start_timer(e.message.results[i].SecondsInStatus, i);
                    //     }

                    //     trs+='<tr><td>'+e.message.results[i].Login+'</td><td>'+e.message.results[i].Campaign+'</td><td>'+e.message.results[i].Subcampaign+'</td><td>'+e.message.results[i].Skill+'</td><td class="instatus_timer">'+e.message.results[i].TimeInStatus+'</td><td>'+e.message.results[i].BreakCode+'</td><td>'+e.message.results[i].State+'</td><td>'+e.message.results[i].Status+'</td></tr>';
                    // }

                    // ran = true;

                    // $('.realtime_table tbody').append(trs);
                });


                // "talking" => array:1 [▶]
                // "wrapping" => []
                // "waiting" => []
                // "manual" => []
                // "paused"
        </script>
    </body>
</html>
