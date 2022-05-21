<a class="mobile-nav-link btn-sm" href="{{ route('home') }}" title="{{ __('Home') }}"
    aria-label="{{ __('Home') }}">
    <i class="fas fa-home fa-fw" aria-hidden="true"></i>
</a>

<a class="mobile-nav-link btn-sm" href="{{ route('explore') }}" title="{{ __('Explore') }}"
    aria-label="{{ __('Explore') }}">
    <i class="fas fa-wand-magic-sparkles fa-fw" aria-hidden="true"></i>
</a>

<a class="mobile-nav-link btn-sm jump-to-top ontop" href="#">
    <i class="fas fa-arrow-turn-up fa-fw d-none" aria-hidden="true"></i>
    <img src="{{ mix('/static/images/logo.svg') }}"
        width="24"
        height="24"
        alt="{{ getSiteConfig('name') }}">
</a>

@auth
    <a class="mobile-nav-link btn-sm position-relative" href="{{ route('notifications.index') }}" title="{{ __('Notifications') }}"
        aria-label="{{ __('Notifications') }}">
        <i class="fas fa-bell fa-fw" aria-hidden="true"></i>

        <span @class([
            'badge',
            'badge-indicator',
            'bg-danger',
            'unread-count',
            'd-none' => auth()->user()->unreadNotifications->count() == 0,
            ])>
            <span class="count">{{ auth()->user()->unreadNotifications->count() }}</span>
            <span class="visually-hidden">{{ __('unread notifications') }}</span>
        </span>
    </a>
@else
    <a class="mobile-nav-link btn-sm" href="{{ route('socialite') }}" title="{{ __('Login') }}"
        aria-label="{{ __('Login') }}" disabled>
        <i class="fas fa-user fa-fw" aria-hidden="true"></i>
    </a>
@endauth

<a class="mobile-nav-link btn-sm"
    href="#" title="{{ __('Toggle sidebar') }}"
    data-bs-toggle="offcanvas"
    data-bs-target="#offcanvas-sidebar-user"
    aria-controls="offcanvas-sidebar-user"
    aria-label="{{ __('Toggle sidebar') }}">
    @auth
        {!! getUserAvatar(auth()->user(), $size = 'sm') !!}
    @else
        <i class="fas fa-bars fa-fw" aria-hidden="true"></i>
    @endauth
</a>
