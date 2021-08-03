@php $type = ('home' === Route::current()->getName()) ? 'hero' : 'standard' @endphp

<header class="{{ $type }}">
    @if ('hero' === $type)
    <div class="container welcome">
        <h1 class="display-1">{{ getSiteConfig('name') }}</h1>
        <p class="lead">{{ getSiteConfig('slogan') }}</p>
        <button class="jump-to-nav btn">
            <i class="fas fa-chevron-down fa-3x" aria-hidden="true"></i>
            <span class="visually-hidden">{{ __('Jump to the main content') }}</span>
        </button>
    </div>
    @endif

    <div class="container">
        @include('partials.nav')
    </div>
</header>
