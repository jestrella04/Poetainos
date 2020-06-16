<section id="header-mini" class="header">
    <div class="header-mini-wrapper">
        @include('partials.nav')

        <div class="header-mini-main d-flex flex-column">
            <div class="header-welcome">
                <h1>{{ getSiteConfig('name') }}</h1>
            </div>

            <div class="flex-shrink-0">
                @include('partials.waves')
            </div>
        </div>
    </div>
</section>

