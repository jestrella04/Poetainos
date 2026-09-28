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

    <!-- PWA -->
    <meta name="theme-color" content="#006971" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ getSiteConfig('name') }}">
    <link rel="apple-touch-icon" href="/images/logo.svg">
    <link rel="manifest" href="{{ route('pwa.manifest', [], false) }}">

    <!-- Vuetify never declares its cascade layer order up front, so layers rank by first appearance.
         The SSR theme stylesheet below opens with `vuetify-utilities`, which would otherwise make every
         utility class (spacing, display, colors) lose to component styles. -->
    <style>@layer vuetify-core, vuetify-components, vuetify-overrides, vuetify-utilities, vuetify-final;</style>

    <!-- Inertia -->
    @inertiaHead

    <!-- Link previews: crawlers (Facebook, WhatsApp...) don't run JS, so without an SSR head the page
         would expose no title or Open Graph tags. The data-inertia keys match PoHead's head-keys, so
         the client head manager replaces these tags instead of duplicating them. A partial reload
         carries only the props it asked for, so it has no site props to fall back to. -->
    @if (isset($page['props']['site']) && app(\Inertia\Ssr\SsrState::class)->dispatch() === null)
        @php
            $meta = $page['props']['meta'] ?? [];
            $site = $page['props']['site'];
            $title = $meta['title'] ?? $site['name'];
            $canonical = $meta['canonical'] ?? null;
            $description = $meta['description'] ?? $site['slogan'];
            $image = $meta['image'] ?? $site['image'];
        @endphp
        <title data-inertia="">{{ $title }}</title>
        @if ($canonical !== null)
            <link rel="canonical" href="{{ $canonical }}" data-inertia="canonical">
            <meta property="og:url" content="{{ $canonical }}" data-inertia="og-url">
            <meta property="twitter:url" content="{{ $canonical }}" data-inertia="tw-url">
        @endif
        <meta name="description" content="{{ $description }}" data-inertia="description">
        <meta property="og:type" content="website" data-inertia="og-type">
        <meta property="og:title" content="{{ $title }}" data-inertia="og-title">
        <meta property="og:description" content="{{ $description }}" data-inertia="og-description">
        <meta property="og:image" content="{{ $image }}" data-inertia="og-image">
        <meta property="twitter:card" content="summary_large_image" data-inertia="tw-card">
        <meta property="twitter:title" content="{{ $title }}" data-inertia="tw-title">
        <meta property="twitter:description" content="{{ $description }}" data-inertia="tw-description">
        <meta property="twitter:image" content="{{ $image }}" data-inertia="tw-image">
    @endif

    <!-- Ziggy/Laravel Routes -->
    @routes(ziggyRouteGroup(), nonce: Vite::cspNonce())

    <!-- Vite -->
    @vite('resources/js/app.ts')

    @if (!empty(config('services.counter.tracking_id')))
        <!-- Counter Stats -->
        <link rel="preconnect" href="https://cdn.counter.dev">
        <script src="https://cdn.counter.dev/script.js" data-id="{{ config('services.counter.tracking_id') }}"
            data-utcoffset="-4" defer></script>
    @endif
</head>

<body>
    @inertia
</body>

</html>