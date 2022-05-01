<nav id="nav-desktop" class="navbar navbar-expand fixed-top d-none d-lg-flex">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ mix('/static/images/logo.svg') }}" width="32" height="32" alt="{{ getSiteConfig('name') }}">
            {{ getSiteConfig('name') }}
        </a>

        <div id="header-navbar" class="collapse navbar-collapse">
            @include('partials.navlinks-desktop')
        </div>
    </div>
</nav>

<nav id="nav-mobile" class="navbar fixed-top d-lg-none">
    <div class="container d-flex justify-content-between">
        @include('partials.navlinks-mobile')
    </div>
</nav>
