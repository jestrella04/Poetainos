<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
    <meta name="author" content="Jonathan Estrella">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Icons -->
    <link rel="icon" href="/images/logo.svg" type="image/svg+xml">
    <link rel="alternate icon" href="/images/logo-32.png" sizes="32x32" type="image/png">

    <!-- PWA Support -->
    <meta name="theme-color" content="#673AB7" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ getSiteConfig('name') }}">
    <link rel="apple-touch-icon" href="/images/logo.svg">
    <link rel="manifest" href="{{ route('pwa.manifest', [], false) }}">

    @inertiaHead
    @routes
    @vite('resources/js/app.js')
    @if (!empty(config('services.counter.tracking_id')))
        <!-- Counter Stats -->
        <script src="https://cdn.counter.dev/script.js" data-id="{{ config('services.counter.tracking_id') }}"
            data-utcoffset="-4"></script>
    @endif
</head>

<body>
    @inertia
</body>

</html>
