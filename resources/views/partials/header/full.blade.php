<section id="header" class="header">
    <div class="header-wrapper">
        @include('partials.nav')

        <div class="header-main d-flex flex-column">
            <div class="header-welcome">
                <h1>{{ getSiteConfig('name') }}</h1>
                <p class="lead">{{ getSiteConfig('slogan') }}</p>
            </div>

            <div class="flex-shrink-0">
                @include('partials.waves')
            </div>
        </div>
    </div>
</section>
