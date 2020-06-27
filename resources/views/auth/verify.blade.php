@extends('layouts.login')

@section('title', __('Verify Account'))

@section('main')
    <div id="register" class="login">
        <div class="form-wrapper">
            @if (session('resent'))
                <div class="alert alert-success" role="alert">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                </div>
            @endif

            <div class="header">
                <h4 class="all-caps">{{ __('You are almost done') }}</h4>
                <p class="lead">{{ __('Please verify your email address') }}</p>
            </div>

            <div class="info">
                {{ __('Before proceeding, please check your email for the verification link that was sent to you') }}.
                {{ __('You may need to also check your Spam folder') }}.
                {{ __('If for some reason you did not receive the email you can request another one clicking the below button') }}.
            </div>

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn btn-dark btn-lg btn-block">{{ __('Request verification') }}</button>
            </form>

            <a href="{{ route('home') }}" class="btn btn-link btn-block email-confirmed">
                <small>{{ __('Already confirmed? Continue to the homepage') }}.</small>
            </a>
        </div>
    </div>
@endsection
