<!doctype html>
<html lang=en-us>
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
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preloader {
           position: fixed;
           top: 0;
           left: 0;
           width: 100%;
           height: 100%;
           z-index: 9999;
           background-image: url('/admindashboard/img/loader2.gif');
           background-repeat: no-repeat; 
           background-color: rgba(255,255,255,0.88);
           background-position: center;
        }
    </style>
    <link href="/css/main.css" rel="stylesheet" type="text/css"/>
    @isset($cssfile)
    @foreach($cssfile as $css)
    <link href="/css/{{ $css }}" rel="stylesheet" type="text/css"/>
    @endforeach
    @endisset
    <link href="/css/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
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

    @yield('content')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js" type="text/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js" type="text/javascript"></script>
    <script src="/js/jquery-ui.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    @isset($jsfile)
    @foreach($jsfile as $js)
    <script src="/js/{{ $js }}" type="text/javascript"></script>
    @endforeach
    @endisset
    <script>
        $(window).load(function() {
            $('.preloader').fadeOut('slow');
        });
    </script>
  </body>
</html>