@extends('layouts.index')

@section('title', __('Verify Account'))

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="register" class="d-flex justify-content-center login">
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
                {{ __('Before proceeding, please check your email for a verification link.') }}
                {{ __('If you did not receive the email') }}...
            </div>

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn btn-dark btn-lg btn-block">{{ __('Request another') }}</button>
            </form>

            <a href="{{ route('home') }}" class="btn btn-link btn-block email-confirmed">
                <small>{{ __('Already confirmed? Continue to the homepage') }}.</small>
            </a>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
