@extends('layouts.login')

@section('meta.title', getPageTitle([__('Reset Password')]))

@section('main')
    <div id="reset-password" class="login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="all-caps">{{ __('You are almost done') }}</h4>
                <p class="text-muted">{{ __('Please set your new password') }}</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <input
                        id="email"
                        type="email"
                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ $email ?? old('email') }}"
                        required
                        readonly>

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <input
                        id="password"
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
                    <input
                        id="password-confirm"
                        type="password"
                        class="form-control form-control-lg"
                        name="password_confirmation"
                        required
                        placeholder="{{ __('Confirm your password') }}"
                        autocomplete="off">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
