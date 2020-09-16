<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!--[if IE 7]>         <html class="ie7"> <![endif]-->
<!--[if IE 8]>         <html class="ie8"> <![endif]--> 
<!--[if IE]>           <html class="ie"> <![endif]--> 

<head>
    <meta charset=utf-8>
    <title>@yield('title')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="_token" content="{{csrf_token()}}" />
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/bootstrap-material-datetimepicker.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    @if(Auth::user()->theme == 'dark')
        <link href="/css/darktheme_app.css" rel="stylesheet" type="text/css"/>
    @else
        <link href="/css/app.css" rel="stylesheet" type="text/css"/>
    @endif
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.0.0/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    {{-- <link href="/css/jquery-ui.min.css" rel="stylesheet"> --}}
    <!--[if lt IE 9]>
    <script src="/js/html5shiv.min.js"></script>
    <script src="/js/respond.min.js"></script>
    <![endif]-->
    <!--[if lt IE 8]>
    <link href="/css/bootstrap-ie7.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE]>
    <script type="text/javascript" src="/js/css3-mediaqueries.js"></script>    
    <![endif]-->
</head>
<body>
    <input type="hidden" class="theme" value="{{Auth::user()->theme}}">

    <div class="preloader"></div>

    <div class="wrapper">

        @if(!session()->has('isSso'))
            @include('shared.sidenav')
        @endif

        <div id="content">

            @include('shared.navbar')

            <div class="container-fluid bg dashboard p20">

                <div class="container-full mt20">
                    <div class="row">
                        <div class="col-sm-12">
                            
                            @yield('content')
                            
                            <div class="reportextras">
                                @yield('extras')
                            </div>
                            {{-- <div class="report_results">
                                <div class="reportpag pag"></div>
                                <div class="reporttable table-responsive {{$report}}"></div>
                                <div class="reportextras">
                                    @yield('extras')
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('shared.notifications_bar')
    </div>
    @include('shared.reportmodal')
    
    <script src="/messages.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js" type="text/javascript"></script>
    {{-- <script src="/js/jquery-ui.min.js" type="text/javascript"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.12/js/dataTables.bootstrap.min.js"></script>
    <script src="/js/moment.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.0.0/js/bootstrap-datetimepicker.min.js"></script>
    {{-- <script src="/js/datetimepicker.js"></script> --}}
    <script src="/js/multiselect_lib.js"></script>
    <script src="/js/multiselect.js"></script>
    <script src="/js/color-hash.js"></script>
    <script src="/js/master.js"></script>
    <script src="/js/nav.js"></script>
    <script src="/js/bootstrap-material-datetimepicker.js"></script>

    @isset($jsfile)
    @foreach($jsfile as $js)
    <script src="/js/{{ $js }}" type="text/javascript"></script>
    @endforeach
    @endisset
    <script>

        $('.datetimepicker').bootstrapMaterialDatePicker({ format : 'MM/DD/YYYY HH:mm', currentDate : new Date() });

        $(window).on('load', function() {
            $('.preloader').fadeOut('slow');
        });

        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });

            $('.btn-notifications, .close_nots_bar').on('click', function () {
                $('#sidebar_nots').toggleClass('sb_open');
            });
        });
    </script>
  </body>
</html>