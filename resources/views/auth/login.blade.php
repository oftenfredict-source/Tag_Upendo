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
    <title>{{ __('Login') }} - {{ $appChurchName ?? 'TAG Upendo' }}</title>
</head>

<body class="login-page">
    <div class="login-page__bg" style="background-image: url('{{ asset('dady.JPG') }}');"></div>
    <div class="login-page__overlay"></div>

    <section class="login-page__content">
        <div class="login-page__brand">
            @if(!empty($appChurchLogo))
                <img src="{{ $appChurchLogo }}" alt="{{ $appChurchName ?? 'TAG Upendo' }}" class="login-logo-img">
            @endif
            <h1>{{ $appChurchName ?? 'TAG Upendo' }}</h1>
            @if(!empty($appChurchTagline))
                <p class="login-tagline">{{ $appChurchTagline }}</p>
            @endif
        </div>

        <div class="login-page__card">
            <form class="login-page__form" action="{{ url('/login') }}" method="POST">
                @csrf
                <div class="login-page__form-head">
                    <span class="login-page__icon"><i class="fa fa-user"></i></span>
                    <div>
                        <h2>{{ __('Welcome back') }}</h2>
                        <p>{{ __('Sign in to continue') }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">{{ __('MEMBER ID OR EMAIL') }}</label>
                    <div class="login-page__input-wrap">
                        <i class="fa fa-envelope-o"></i>
                        <input class="form-control" name="login" type="text" placeholder="{{ __('Member ID or Email') }}"
                            autofocus required value="{{ old('login') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">{{ __('PASSWORD') }}</label>
                    <div class="login-page__input-wrap login-page__input-wrap--password">
                        <i class="fa fa-lock"></i>
                        <input class="form-control" id="loginPassword" name="password" type="password" placeholder="{{ __('Password') }}" required>
                        <button type="button" class="login-page__password-toggle" id="toggleLoginPassword"
                            aria-label="{{ __('Show password') }}" title="{{ __('Show password') }}">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <small class="login-page__hint">{{ __('Members: use your last name in CAPITAL letters') }}</small>
                </div>

                <div class="form-group mb-4">
                    <label class="login-page__remember">
                        <input type="checkbox" name="remember">
                        <span>{{ __('Stay Signed in') }}</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block login-page__submit">
                    <i class="fa fa-sign-in"></i> {{ __('SIGN IN') }}
                </button>
            </form>
        </div>

        <p class="login-page__footer">&copy; {{ date('Y') }} {{ $appChurchName ?? 'TAG Upendo' }}</p>
    </section>

    <script src="{{ asset('vali-master/docs/js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/popper.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var passwordInput = document.getElementById('loginPassword');
        var toggleBtn = document.getElementById('toggleLoginPassword');

        if (passwordInput && toggleBtn) {
            var showLabel = @json(__('Show password'));
            var hideLabel = @json(__('Hide password'));

            toggleBtn.addEventListener('click', function () {
                var isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';

                var icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye', !isHidden);
                    icon.classList.toggle('fa-eye-slash', isHidden);
                }

                toggleBtn.setAttribute('aria-label', isHidden ? hideLabel : showLabel);
                toggleBtn.setAttribute('title', isHidden ? hideLabel : showLabel);
            });
        }

        @if ($errors->any())
        Swal.fire({
            icon: 'error',
            title: @json(__('Login failed')),
            html: {!! json_encode(implode('<br>', $errors->all())) !!},
            confirmButtonColor: '#940000'
        });
        @endif
    });
    </script>
</body>

</html>
