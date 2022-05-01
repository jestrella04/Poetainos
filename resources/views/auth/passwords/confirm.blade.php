@extends('layouts.login')

@section('meta.title', getPageTitle([__('Confirm Password')]))

@section('main')
    <div id="confirm-password" class="login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="block-title">{{ __('Please confirm your password before continuing.') }}</h4>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input
                        id="password"
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
                    <button type="submit" class="btn btn-primary btn-lg">
                        {{ __('Confirm Password') }}
                    </button>

                    @if (Route::has('password.request'))
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
