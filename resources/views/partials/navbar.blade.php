<nav id="nav-main" class="navbar navbar-dark navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ mix('/static/images/logo.svg') }}" width="32" height="32" alt="{{ getSiteConfig('name') }}">
            {{ getSiteConfig('name') }}
        </a>

        <div class="toggler-wrapper">
            <button
                id="toggler"
                class="rounded navbar-toggler"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvas-sidebar-user"
                aria-controls="offcanvas-sidebar-user"
                aria-label="{{ __('Toggle sidebar') }}">

                <div class="icon-badge">
                    @php $display = (auth()->check() && auth()->user()->unreadNotifications->count() > 0) ? '' : 'd-none' @endphp
                    <i class="fa fa-bars" aria-hidden="true"></i>
                    <span class="translate-middle p-2 border border-light rounded-circle unread {{ $display }}">
                        <span class="visually-hidden">{{ __('Unread notifications') }}</span>
                    </span>
                </div>
            </button>
        </div>

        <div id="header-navbar" class="collapse navbar-collapse">
            @include('partials.navlinks')
        </div>
    </div>
</nav>
