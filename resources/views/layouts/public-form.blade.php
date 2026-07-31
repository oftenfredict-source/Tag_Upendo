<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="{{ asset('vali-master/docs/css/main.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/brand.css') }}">
    <link rel="stylesheet" type="text/css"
        href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>@yield('title', __('Member Registration')) - {{ $appChurchName ?? 'TAG Upendo' }}</title>
    @stack('styles')
</head>

<body class="login-page public-form-page">
    <div class="login-page__bg" style="background-image: url('{{ asset('dady.JPG') }}');"></div>
    <div class="login-page__overlay"></div>

    <section class="login-page__content public-form-page__content">
        <div class="login-page__brand">
            @if(!empty($appChurchLogo))
                <img src="{{ $appChurchLogo }}" alt="{{ $appChurchName ?? 'TAG Upendo' }}" class="login-logo-img">
            @endif
            <h1>{{ $appChurchName ?? 'TAG Upendo' }}</h1>
            @if(!empty($appChurchTagline))
                <p class="login-tagline">{{ $appChurchTagline }}</p>
            @endif
        </div>

        <div class="public-form-page__card">
            @yield('content')
        </div>

        <p class="login-page__footer">&copy; {{ date('Y') }} {{ $appChurchName ?? 'TAG Upendo' }}</p>
    </section>

    <script src="{{ asset('vali-master/docs/js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/popper.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/bootstrap.min.js') }}"></script>
    @stack('scripts')
</body>

</html>
