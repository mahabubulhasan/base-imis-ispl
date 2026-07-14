<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IMIS') }}</title>

    <!-- Scripts -->
    <script type="javascript" src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
</head>
<body class="login-page">
    <div class="login-box">

    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <h3 style="font-weight: bold;">Integrated Municipal Information System</h3>
        </div>
        <div class="card-body">
        @yield('content')
        </div>
    </div>
        </div>
</body>
</html>
