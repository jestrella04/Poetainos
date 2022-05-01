@extends('layouts.login')

@section('meta.title', getPageTitle([__('Verify Account')]))

@section('main')
    <div id="register" class="login">
        <div class="form-wrapper">
            @if (session('resent'))
                <div class="alert alert-success" role="alert">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                </div>
            @endif

            <div class="header">
                <h4 class="block-title">{{ __('Please verify your email address') }}</h4>
            </div>

            <div class="info">
                {{ __('Before proceeding, please check your email for the verification link that was sent to you') }}.
                {{ __('You may need to also check your Spam folder') }}.
                {{ __('If for some reason you did not receive the email you can request another one clicking the below button') }}.
            </div>

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">{{ __('Request verification') }}</button>
                </div>
            </form>

            <div class="d-grid gap-2 mb-3">
                <a href="{{ route('home') }}" class="btn btn-link email-confirmed">
                    <small>{{ __('Already confirmed? Continue to the homepage') }}.</small>
                </a>
            </div>
        </div>
    </div>
@endsection
