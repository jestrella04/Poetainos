@extends('layouts.index')

@section('title', __('Create Account'))

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="register" class="d-flex justify-content-center login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="all-caps">{{ __('Join the hood') }}</h4>
                <p class="text-muted">{{ __('Creating an account is fast and easy') }}</p>
            </div>

            <div class="social">
                @include('partials.socialite')
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <input id="username"
                        type="text"
                        class="form-control form-control-lg @error('username') is-invalid @enderror"
                        name="username" value="{{ old('username') }}"
                        required
                        autocomplete="username"
                        autofocus
                        pattern="[A-Za-z0-9]+"
                        placeholder="{{ __('Enter your username') }}">

                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <input id="email"
                        type="email"
                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="{{ __('Enter your email') }}">

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <input id="password"
                        type="password"
                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="{{ __('Enter your password') }}">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <input id="password-confirm"
                        type="password"
                        class="form-control form-control-lg"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="{{ __('Confirm your password') }}">
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap">
                        <button type="submit" class="btn btn-dark btn-lg btn-block">
                            {{ __('Create account') }}
                        </button>

                        <a href="{{ route('login') }}" class="btn btn-success btn-lg btn-block">{{ __('Login') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
