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
    <meta name="es.tools.tools" >
    <meta name="_token" content="{{csrf_token()}}" />
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    
    @auth
        @if(Auth::user()->theme == 'dark')
            <link href="/css/darktheme_app.css" rel="stylesheet" type="text/css"/>
        @else
            <link href="/css/app.css" rel="stylesheet" type="text/css"/>
        @endif
    @endauth
    @guest
        <link href="/css/app.css" rel="stylesheet" type="text/css"/>
    @endguest
    
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
    <input type="hidden" class="theme" value="{{(Auth::check()) ? Auth::user()->theme : ''}}">

    @yield('content')

  </body>
</html>