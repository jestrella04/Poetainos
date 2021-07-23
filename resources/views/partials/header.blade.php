@php $type = ('home' === Route::current()->getName()) ? 'hero' : 'header' @endphp

<section id="{{ $type }}" class="header">
    @if ('hero' === $type)
    <div class="container welcome">
        <h1 class="display-1">{{ getSiteConfig('name') }}</h1>
        <p class="lead">{{ getSiteConfig('slogan') }}</p>
        <a id="jump-to-nav" href="#nav-main" aria-label="{{ __('Jump to the main content') }}">
            <i class="fas fa-chevron-down fa-3x" aria-hidden="true"></i>
        </a>
    </div>
    @endif

    <div class="container">
        @include('partials.nav')
    </div>
</section>
