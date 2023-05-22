<a class="nav-link flex-fill" href="{{ route('home') }}" title="{{ __('Home') }}"
    aria-label="{{ __('Home') }}">
    <i class="fas fa-home fa-fw" aria-hidden="true"></i>
</a>

<a class="nav-link flex-fill" href="{{ route('explore') }}" title="{{ __('Explore') }}"
    aria-label="{{ __('Explore') }}">
    <i class="fas fa-wand-magic-sparkles fa-fw" aria-hidden="true"></i>
</a>

<a class="nav-link flex-fill" href="{{ route('writings.create') }}" title="{{ __('Publish') }}">
    <i class="fas fa-pen-nib fa-fw" aria-hidden="true"></i>
</a>

@auth
    <a class="nav-link flex-fill position-relative"
        href="{{ route('notifications.list.unread') }}"
        rel="noindex"
        title="{{ __('Notifications') }}"
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
    <a class="nav-link flex-fill" href="{{ route('socialite') }}" title="{{ __('Login') }}"
        aria-label="{{ __('Login') }}" disabled>
        <i class="fas fa-user fa-fw" aria-hidden="true"></i>
    </a>
@endauth

<a class="nav-link flex-fill"
    href="#" title="{{ __('Toggle sidebar') }}"
    data-bs-toggle="offcanvas"
    data-bs-target="#offcanvas-sidebar-user"
    aria-controls="offcanvas-sidebar-user"
    aria-label="{{ __('Toggle sidebar') }}">
    @auth
        {!! getUserAvatar(auth()->user(), $size = 'ms') !!}
    @else
        <i class="fas fa-bars fa-fw" aria-hidden="true"></i>
    @endauth
</a>
