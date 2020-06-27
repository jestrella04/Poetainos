@extends('layouts.master')

@section('body')
    <main role="main" class="login-wrapper">
        <div class="logo">
            <div>
                <a href="{{ route('home') }}">
                    <img src="{{ mix('/static/images/logo-64.png') }}" width="64" height="64" alt="{{ getSiteConfig('name') }}">
                    <h1>{{ getSiteConfig('name') }}</h1>
                </a>
            </div>
        </div>

        @yield ('main')
    </main>
@endsection
