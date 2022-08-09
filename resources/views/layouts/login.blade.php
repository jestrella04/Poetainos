@extends('layouts.master')

@section('body')
    <main role="main" class="login-wrapper d-flex flex-wrap flex-lg-row-reverse">
        <div class="logo">
            <img src="{{ mix('/static/images/logo.svg') }}" alt="{{ getSiteConfig('name') }}" class="login-logo logo-shadow">
        </div>

        @yield ('main')
    </main>

    <footer></footer>
@endsection
