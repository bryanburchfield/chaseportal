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
                <table border="1">
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
                </table>
            </div>
        </div>
        <script src="{{asset('js/app.js')}}"></script>
        <script>
            Echo.channel('{{ $channel }}')
                .listen('NewMessage', (e) => {
                    console.log(e.message);
                })
        </script>
    </body>
</html>
