@extends('layouts.index')

@section('title', __('Confirm Password'))

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="confirm-password" class="d-flex justify-content-center">
        <div class="wrapper">
            <div class="header">
                <h4 class="all-caps">{{ __('Confirm Password') }}</h4>
                <p class="text-muted">{{ __('Please confirm your password before continuing.') }}</p>
            </div>

            <div class="body">
                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <div class="form-group">
                        <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required placeholder="{{ __('Enter your password') }}">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-dark btn-block">
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
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
