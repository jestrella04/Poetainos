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
                <h4 class="all-caps">{{ __('Verify your email address') }}</h4>
            </div>

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                {{ __('Before proceeding, please check your email for a verification link.') }}
                {{ __('If you did not receive the email') }},
                <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
            </form>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
