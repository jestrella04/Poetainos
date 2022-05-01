@auth
    <div class="dropdown">
        <a class="mobile-nav-link btn-sm" href="#" title="{{ __('My profile') }}"
            aria-label="{{ __('My profile') }}"
            data-bs-toggle="dropdown"
            data-bs-offset="0,18"
            aria-haspopup="true"
            aria-expanded="false">
            {!! getUserAvatar(auth()->user(), $size = 'sm') !!}
        </a>

        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ auth()->user()->path() }}">{{ __('My profile') }}</a>
            <a class="dropdown-item" href="{{ auth()->user()->writingsPath() }}">{{ __('My writings') }}</a>
            <a class="dropdown-item" href="{{ auth()->user()->shelfPath() }}">{{ __('My shelf') }}</a>
            <form class="d-inline" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn dropdown-item">{{ __('Logout') }}</button>
            </form>
        </div>
    </div>
@else
    <a class="mobile-nav-link btn-sm" href="{{ route('socialite') }}" title="{{ __('Login') }}"
        aria-label="{{ __('Login') }}">
        <i class="fas fa-user fa-fw" aria-hidden="true"></i>
    </a>
@endauth

<a class="mobile-nav-link btn-sm" href="{{ route('explore') }}" title="{{ __('Explore') }}"
    aria-label="{{ __('Explore') }}">
    <i class="fas fa-wand-magic-sparkles fa-fw" aria-hidden="true"></i>
</a>

<a class="mobile-nav-link btn-sm jump-to-top" href="#">
    <img src="{{ mix('/static/images/logo.svg') }}" width="24" height="24"
        alt="{{ getSiteConfig('name') }}">
</a>

@auth
    <a class="mobile-nav-link btn-sm position-relative" href="{{ route('notifications.index') }}" title="{{ __('Notifications') }}"
        aria-label="{{ __('Notifications') }}">
        <i class="fas fa-bell fa-fw" aria-hidden="true"></i>

        @php $display = (auth()->check() && auth()->user()->unreadNotifications->count() > 0) ? '' : 'd-none' @endphp
        <span class="badge-indicator bg-danger unread {{ $display }}">
            {{ auth()->user()->unreadNotifications->count() ?: '' }}
            <span class="visually-hidden">{{ __('unread notifications') }}</span>
        </span>
    </a>
@else
    <a class="mobile-nav-link btn-sm" href="{{ route('notifications.index') }}" title="{{ __('Notifications') }}"
        aria-label="{{ __('Notifications') }}" disabled>
        <i class="fas fa-bell fa-fw" aria-hidden="true"></i>
    </a>
@endauth

<a class="mobile-nav-link btn-sm"
    href="#" title="{{ __('Toggle sidebar') }}"
    data-bs-toggle="offcanvas"
    data-bs-target="#offcanvas-sidebar-user"
    aria-controls="offcanvas-sidebar-user"
    aria-label="{{ __('Toggle sidebar') }}">
    <i class="fas fa-bars fa-fw" aria-hidden="true"></i>
</a>
