<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="@yield('meta.description', getSiteConfig('slogan'))">
        <meta name="keywords" content="@yield('meta.keywords', __('writings, poetry, poems, short stories, writers, amateur writers, writerhood, writers hood, poets'))">
        <meta name="author" content="Jonathan Estrella">
        <meta name="generator" content="Writerhood">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('meta.title') - {{ getSiteConfig('name') }}</title>

        <link rel="preload" href="{{ mix('/static/css/app.css') }}" as="style">
        <link rel="preload" href="{{ mix('/static/js/app.js') }}" as="script">
        <link rel="icon" href="{{ mix('/static/images/logo.svg') }}" type="image/svg+xml">
        <link rel="alternate icon" href="{{ mix('/static/images/logo-32.png') }}" sizes="32x32" type="image/png">
        <link rel="stylesheet" href="{{ mix('/static/css/app.css') }}" type="text/css">

        {{-- PWA Support --}}
        <meta name="theme-color" content="#2F3BA2" />
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="{{ getSiteConfig('name') }}">
        <link rel="apple-touch-icon" href="{{ mix('/static/images/logo.svg') }}">
        <link rel="manifest" href="{{ route('pwa.manifest') }}">

        <base href="{{ config('app.url') }}">

        <script src="{{ mix('/static/js/app.js') }}" defer></script>
        @if (! empty(config('services.google.analytics_id')))
            @include('partials.analytics')
        @endif
        @yield('scripts')
    </head>

    <body class="d-flex flex-column overflow-hidden">
        @include('partials.noscript')
        @include('partials.toast')

        @yield('body')
    </body>
</html>
