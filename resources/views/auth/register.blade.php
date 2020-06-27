@extends('layouts.login')

@section('title', __('Create account'))

@section('main')
    <div id="register" class="login">
        <div class="form-wrapper">
            <div class="header">
                <h4 class="all-caps">{{ __('Join the hood') }}</h4>
                <p class="text-muted">{{ __('Creating an account is fast and easy') }}</p>
            </div>

            <div class="social">
                @include('partials.socialite')
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <input id="username"
                        type="text"
                        class="form-control form-control-lg @error('username') is-invalid @enderror toggle-popover-register"
                        name="username" value="{{ old('username') }}"
                        required
                        autocomplete="username"
                        pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
                        placeholder="{{ __('Enter your username') }}">

                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror

                    <div class="d-none help">
                        <ul class="list-unstyled mb-0">
                            <li>{{ __('Must be between 3 and 45 characters long') }}.</li>
                            <li>{{ __('Can contain letters, numbers, underscores and periods') }}.</li>
                            <li>{{ __('Cannot start with a period nor end with a period') }}.</li>
                            <li>{{ __('It must also not have more than one period sequentially') }}.</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <input id="email"
                        type="email"
                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="{{ __('Enter your email') }}">

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <input id="password"
                        type="password"
                        class="form-control form-control-lg @error('password') is-invalid @enderror toggle-popover-register"
                        name="password"
                        required
                        autocomplete="new-password"
                        pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
                        placeholder="{{ __('Enter your password') }}">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror

                    <div class="d-none help">
                        <ul class="list-unstyled mb-0">
                            <li>{{ __('Must be at least 8 characters long') }}.</li>
                            <li>{{ __('Must contain at least one upper case letter, one lower case letter and one number') }}.</li>
                            <li>{{ __('Must contain at least one special character') }}.</li>
                            <li>{{ __('Cannot contain spaces or emojis') }}.</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <input id="password-confirm"
                        type="password"
                        class="form-control form-control-lg toggle-popover-register"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
                        placeholder="{{ __('Confirm your password') }}">

                    <div class="d-none help">
                        <ul class="list-unstyled mb-0">
                            <li>{{ __('Must be at least 8 characters long') }}.</li>
                            <li>{{ __('Must contain at least one upper case letter, one lower case letter and one number') }}.</li>
                            <li>{{ __('Must contain at least one special character') }}.</li>
                            <li>{{ __('Cannot contain spaces or emojis') }}.</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap">
                        <button type="submit" class="btn btn-dark btn-lg btn-block">
                            {{ __('Create account') }}
                        </button>

                        <a href="{{ route('login') }}" class="btn btn-success btn-lg btn-block">{{ __('Login') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
