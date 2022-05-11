<nav id="nav-desktop" class="navbar navbar-dark navbar-expand-lg fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ mix('/static/images/logo.svg') }}" width="32" height="32" alt="{{ getSiteConfig('name') }}">
            {{ getSiteConfig('name') }}
        </a>

        <div class="toggler-wrapper">
            <button
                id="toggler"
                class="rounded navbar-toggler"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvas-sidebar-admin"
                aria-controls="offcanvas-sidebar-admin"
                aria-label="{{ __('Toggle sidebar') }}">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </button>
        </div>

        <div id="header-navbar" class="collapse navbar-collapse">
            @include('admin.partials.navlinks')
        </div>
    </div>
</nav>
