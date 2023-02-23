@extends('layouts.login')

@section('meta.title', getPageTitle([__('Password Recovery')]))

@section('main')
    <div id="email-password" class="login">
        <div class="form-wrapper">

            <div class="header">
                <h4 class="block-title">{{ __('Let\'s validate your email address') }}</h4>
            </div>

            <form method="POST" action="{{ route('login.email.post') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input
                        id="email"
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

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        {{ __('Continue') }}
                    </button>
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
