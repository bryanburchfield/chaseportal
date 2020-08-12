<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{csrf_token()}}" />

        <title>Laravel</title>

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
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                    <div class="col-sm-offset-10 col-sm-2 mb20"><div id="txt"></div></div>
                <div class="responsive-table">
                        <table border="1" class="table realtime_table">
                            <thead>
                                <tr>
                                    <th>Login</th>
                                    <th>Campaign</th>
                                    <th>Subcampaign</th>
                                    <th>Skill</th>
                                    <th>TimeInStatus</th>
                                    <th>BreakCode</th>
                                    <th>State</th>
                                    <th>Status</th>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($data['results'] as $row)
                                    <tr>
                                        <td>{{ $row['Login']}}</td>
                                        <td>{{ $row['Campaign']}}</td>
                                        <td>{{ $row['Subcampaign']}}</td>
                                        <td>{{ $row['Skill']}}</td>
                                        <td>{{ $row['TimeInStatus']}}</td>
                                        <td>{{ $row['BreakCode']}}</td>
                                        <td>{{ $row['State']}}</td>
                                        <td>{{ $row['Status']}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
        <script src="{{asset('js/app.js')}}"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js" type="text/javascript"></script>
        <script>
            Echo.channel('{{ $channel }}')
                .listen('NewMessage', (e) => {
                    console.log(e.message);
                    $('.realtime_table tbody').empty();
                    var trs;
                    for(var i=0;i<e.message.results.length;i++){
                        trs+='<tr><td>'+e.message.results[i].Login+'</td><td>'+e.message.results[i].Campaign+'</td><td>'+e.message.results[i].Subcampaign+'</td><td>'+e.message.results[i].Skill+'</td><td>'+e.message.results[i].TimeInStatus+'</td><td>'+e.message.results[i].BreakCode+'</td><td>'+e.message.results[i].State+'</td><td>'+e.message.results[i].Status+'</td>';
                    }

                    $('.realtime_table tbody').append(trs);
                })
        </script>
    </body>
</html>
