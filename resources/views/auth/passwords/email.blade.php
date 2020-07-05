@extends('layouts.login')

@section('meta.title', __('Password Recovery'))

@section('main')
    <div id="email-password" class="login">
        <div class="form-wrapper">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="header">
                <h4 class="all-caps">{{ __('So you forgot your password') }}</h4>
                <p class="text-muted">{{ __("No problem, let's reset it") }}</p>
            </div>

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <input
                        id="email"
                        type="email"
                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}"
                        required
                        placeholder="{{ __('Enter your email address') }}"
                        autocomplete="off">

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        {{ __('Reset password') }}
                    </button>

                    <a href="{{ route('login') }}" class="btn btn-dark btn-lg btn-block">{{ __('Login') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
