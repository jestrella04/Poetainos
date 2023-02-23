@extends('layouts.login')

@section('meta.title', getPageTitle([__('Login')]))

@section('main')
    <div id="login" class="login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="block-title">{{ __('Welcome back!') }}</h4>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <input class="form-check-input"
                        type="hidden"
                        name="remember"
                        id="remember"
                        value="on">

                <div class="form-floating mb-3">
                    <input id="email"
                        type="email"
                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}"
                        required
                        placeholder="{{ __('Email') }}"
                        autocomplete="off">

                    <label for="email">{{ __('Email') }}</label>

                    @error('email')
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

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-lg btn-primary">
                        {{ __('Login') }}
                    </button>
                </div>

                <div class="d-grid gap-2">
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
