@extends('layouts.index')

@section('title', __('Reset Password'))

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="reset-password" class="d-flex justify-content-center login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="all-caps">{{ __('You are almost done') }}</h4>
                <p class="text-muted">{{ __('Please set your new password') }}</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="{{ __('Enter your password') }}">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <input id="password-confirm" type="password" class="form-control form-control-lg" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm your password') }}">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-dark btn-block">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
