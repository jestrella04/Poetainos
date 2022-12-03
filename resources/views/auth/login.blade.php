@extends('layouts.login')

@section('meta.title', getPageTitle([__('Login')]))

@section('main')
    <div id="login" class="login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="block-title">{{ __('Welcome to the hood') }}</h4>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <input class="form-check-input"
                        type="hidden"
                        name="remember"
                        id="remember"
                        value="on">

                <div class="form-floating mb-3">
                    <input id="username"
                        type="text"
                        class="form-control form-control-lg @error('username') is-invalid @enderror"
                        name="username" value="{{ old('username') }}"
                        required
                        pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
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

                {{-- <div class="form-check form-switch mb-3">
                    <input id="service-agreement"
                        type="checkbox"
                        class="form-check-input"
                        name="service_agreement"
                        required
                        role="switch">

                    <label class="form-check-label" for="service-agreement">{{ __('I accept the terms of use') }}.</label>

                    <a href="pages/condiciones-de-uso">
                        <i class="fa fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="form-check form-switch mb-3">
                    <input id="privacy-agreement"
                        type="checkbox"
                        class="form-check-input"
                        name="privacy_agreement"
                        required
                        role="switch">

                    <label class="form-check-label" for="privacy-agreement">{{ __('I accept the privacy policy') }}.</label>

                    <a href="pages/politicas-de-privacidad">
                        <i class="fa fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                </div> --}}

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-lg btn-primary">
                        {{ __('Login') }}
                    </button>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('register') }}" class="btn btn-sm btn-link">
                        {{ __('Don\'t you have an account?') }}
                    </a>

                    <a href="{{ route('password.request') }}" class="btn btn-sm btn-link">
                        {{ __('Forgot your password?') }}
                    </a>
                </div>
            </form>

            <div class="text-center mt-5">
                <a href="{{ route('socialite') }}" class="btn btn-sm btn-outline-secondary" aria-label="{{ __('Go back') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
@endsection
