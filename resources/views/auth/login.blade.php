@extends('layouts.login')

@section('title', __('Login'))

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

                <div class="form-group">
                    <input id="username"
                        type="text"
                        class="form-control form-control-lg @error('username') is-invalid @enderror"
                        name="username" value="{{ old('username') }}"
                        required
                        pattern="[-._A-Za-z0-9]+"
                        placeholder="{{ __('Enter your username') }}"
                        autocomplete="off">

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
                        placeholder="{{ __('Enter your password') }}"
                        autocomplete="off">

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
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        {{ __('Login') }}
                    </button>

                    <a href="{{ route('register') }}" class="btn btn-dark btn-lg btn-block">
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
