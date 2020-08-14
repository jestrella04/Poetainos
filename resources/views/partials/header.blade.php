@php $type = ('home' === Route::current()->getName()) ? 'hero' : 'header' @endphp

<section id="{{ $type }}" class="header">
    <div class="welcome">
        <h1>{{ getSiteConfig('name') }}</h1>

        @if ('hero' === $type)
            <p class="lead">{{ getSiteConfig('slogan') }}</p>
            <a id="jump-to-nav" href="#nav-main">
                <i class="fas fa-chevron-down fa-3x"></i>
            </a>
        @endif
    </div>

    @include('partials.nav')
</section>
