@extends('layouts.login')

@section('meta.title', getPageTitle([__('Confirm Password')]))

@section('main')
    <div id="confirm-password" class="login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="all-caps">{{ __('Confirm Password') }}</h4>
                <p class="text-muted">{{ __('Please confirm your password before continuing.') }}</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

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
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        {{ __('Confirm Password') }}
                    </button>

                    @if (Route::has('password.request'))
                        <a class="btn btn-link btn-block" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
