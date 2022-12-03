@extends('layouts.login')

@section('meta.title', getPageTitle([__('Login')]))

@section('main')
    <div id="login" class="login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="block-title">{{ __('Welcome to the hood') }}</h4>
            </div>

            @include('partials.socialite')
        </div>
    </div>
@endsection
