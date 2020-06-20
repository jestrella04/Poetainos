@extends('layouts.index')

@section('title', __('Login'))

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="login" class="d-flex justify-content-center login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="all-caps">{{ __('Long time no see you') }}</h4>
                <p class="text-muted">{{ __('Welcome back to the hood') }}</p>
            </div>

            <div class="social">
                @include('partials.socialite')
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <input id="username"
                        type="text"
                        class="form-control form-control-lg @error('username') is-invalid @enderror"
                        name="username" value="{{ old('username') }}"
                        required
                        autocomplete="username"
                        pattern="[A-Za-z0-9]+"
                        placeholder="{{ __('Enter your username') }}">

                    @error('username')
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
                        autocomplete="current-password"
                        placeholder="{{ __('Enter your password') }}">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-check text-center">
                        <input class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="remember" {{ old('remember') ? 'checked' : '' }}>

                        <label class="form-check-label" for="remember">
                            {{ __('Keep me logged in') }}
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-dark btn-lg btn-block">
                        {{ __('Login') }}
                    </button>

                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg btn-block">
                        {{ __('Create account') }}
                    </a>
                </div>

                @if (Route::has('password.request'))
                    <div class="form-group text-center">
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            <small>{{ __('Forgot your password?') }}</small>
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
