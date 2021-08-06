@extends('layouts.login')

@section('meta.title', getPageTitle([__('Login')]))

@section('main')
    <div id="login" class="login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="all-caps">{{ __('Login to the hood') }}</h4>
                <p class="text-muted">{{ __('Long time no see you') }}</p>
            </div>

            <div class="social">
                @include('partials.socialite')
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input id="username"
                        type="text"
                        class="form-control form-control-lg @error('username') is-invalid @enderror"
                        name="username" value="{{ old('username') }}"
                        required
                        pattern="[-._A-Za-z0-9]+"
                        placeholder="{{ __('Username') }}"
                        autocomplete="off">

                    <label for="username">{{ __('Username') }}</label>

                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input id="password"
                        type="password"
                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                        name="password"
                        required
                        placeholder="{{ __('Password') }}"
                        autocomplete="off">

                    <label for="password">{{ __('Password') }}</label>

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input"
                        type="checkbox"
                        name="remember"
                        id="remember"
                        {{ old('remember', 'on') ? 'checked' : '' }}>

                    <label class="form-check-label" for="remember">
                        {{ __('Keep me logged in') }}
                    </label>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        {{ __('Login') }}
                    </button>

                    <a href="{{ route('register') }}" class="btn btn-dark btn-lg">
                        {{ __('Create account') }}
                    </a>
                </div>

                @if (Route::has('password.request'))
                    <div class=" text-center">
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            <small>{{ __('Forgot your password?') }}</small>
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
