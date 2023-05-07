<ul class="navbar-nav ms-auto">
    {{-- <li class="nav-item {{ Route::current()->getName() === 'explore' ? 'active' : '' }}">
        <a class="nav-link " href="{{ route('explore') }}">
            <i class="fas fa-wand-magic-sparkles fa-fw" aria-hidden="true"></i>
            {{ __('Explore') }}
        </a>
    </li> --}}

    <li class="nav-item {{ Route::current()->getName() === 'users.index' ? 'active' : '' }}">
        <a class="nav-link " href="{{ route('users.index') }}">
            <i class="fas fa-users fa-fw" aria-hidden="true"></i>
            {{ __('Writers') }}
        </a>
    </li>

    <li class="nav-item {{ Route::current()->getName() === 'writings.awards' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('writings.awards') }}">
            <i class="fas fa-fan fa-fw" aria-hidden="true"></i>
            {{ __('Golden Flowers') }}
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('writings.random') }}" rel="noindex">
            <i class="fas fa-random fa-fw" aria-hidden="true"></i>
            {{ __('Random') }}
        </a>
    </li>

    <li class="nav-item {{ Route::current()->getName() === 'writings.create' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('writings.create') }}">
            <i class="fas fa-pen-nib fa-fw" aria-hidden="true"></i>
            {{ __('Publish') }}
        </a>
    </li>

    @auth
        <li class="nav-item">
            <div class="dropdown">
                <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    {!! getUserAvatar(auth()->user(), $size = 'ms') !!}

                    <span @class([
                        'badge',
                        'badge-indicator',
                        'bg-danger',
                        'unread-count',
                        'd-none' =>
                            auth()->user()->unreadNotifications->count() == 0,
                    ])>
                        <span class="count">{{ auth()->user()->unreadNotifications->count() }}</span>
                        <span class="visually-hidden">{{ __('unread notifications') }}</span>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('users.account', auth()->user()) }}">{{ __('My account') }}</a>

                    <a class="dropdown-item" href="{{ route('notifications.list.unread') }}" rel="noindex">
                        {{ __('Notifications') }}

                        <span @class([
                            'badge',
                            'rounded-pill',
                            'bg-danger',
                            'ms-3',
                            'unread-count',
                            'd-none' =>
                                auth()->user()->unreadNotifications->count() == 0,
                        ])>
                            <span class="count">{{ auth()->user()->unreadNotifications->count() }}</span>
                            <span class="visually-hidden">{{ __('unread notifications') }}</span>
                        </span>
                    </a>

                    @if (auth()->user()->isAllowed('admin'))
                        <a class="dropdown-item" href="{{ route('admin.index') }}">{{ __('Administration') }}</a>
                    @endif
                </div>
            </div>
        </li>
    @else
        <li class="nav-item {{ Route::current()->getName() === 'login' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('socialite') }}">
                <i class="fas fa-user fa-fw" aria-hidden="true"></i>
                {{ __('Login') }}
            </a>
        </li>
    @endauth
</ul>
