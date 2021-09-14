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
                data-wh-source="#header-navbar"
                data-wh-target="#user-side-menu"
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
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ route('notifications.index') }}">
                            <i class="fas fa-bell fa-fw" aria-hidden="true"></i>
                            {{ __('Notifications') }}

                            <span class="badge bg-light ms-3 unread-count">{{ auth()->user()->unreadNotifications->count() ?: '' }}</span>
                            <span class="visually-hidden">{{ __('Unread notifications') }}</span>
                        </a>
                    </li>
                @endauth

                @if (auth()->check() && auth()->user()->isAllowed('admin'))
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ route('admin.index') }}">
                            <i class="fas fa-cogs fa-fw" aria-hidden="true"></i>
                            {{ __('Administration') }}
                        </a>
                    </li>
                @endif

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
                    <a class="nav-link" href="{{ route('writings.random') }}">
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
                    <li class="nav-item d-none d-lg-block">
                        <div class="dropdown">
                            <a class="nav-link" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="icon-badge">
                                    {!! getUserAvatar(auth()->user(), $size = 'md') !!}

                                    @php $display = (auth()->check() && auth()->user()->unreadNotifications->count() > 0) ? '' : 'd-none' @endphp
                                    <span class="translate-middle p-2 border border-light rounded-circle unread {{ $display }}">
                                        <span class="visually-hidden">{{ __('Unread notifications') }}</span>
                                    </span>
                                </div>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end">
                                @if (auth()->user()->isAllowed('admin'))
                                    <a class="dropdown-item" href="{{ route('admin.index') }}">{{ __('Administration') }}</a>
                                @endif
                                <a class="dropdown-item" href="{{ route('notifications.index') }}">
                                    {{ __('Notifications') }}

                                    <span class="badge bg-dark ms-3 unread-count">{{ auth()->user()->unreadNotifications->count() ?: '' }}</span>
                                    <span class="visually-hidden">{{ __('Unread notifications') }}</span>
                                </a>
                                <a class="dropdown-item" href="{{ auth()->user()->path() }}">{{ __('My profile') }}</a>
                                <a class="dropdown-item" href="{{ auth()->user()->writingsPath() }}">{{ __('My writings') }}</a>
                                <a class="dropdown-item" href="{{ auth()->user()->shelfPath() }}">{{ __('My shelf') }}</a>
                                <form class="d-inline" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn dropdown-item">{{ __('Logout') }}</button>
                                </form>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ auth()->user()->path() }}">
                            <i class="fas fa-user-circle fa-fw" aria-hidden="true"></i>
                            {{ __('My profile') }}
                        </a>
                    </li>

                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ auth()->user()->writingsPath() }}">
                            <i class="fas fa-feather fa-fw" aria-hidden="true"></i>
                            {{ __('My writings') }}
                        </a>
                    </li>

                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ auth()->user()->shelfPath() }}">
                            <i class="fas fa-book-reader fa-fw" aria-hidden="true"></i>
                            {{ __('My shelf') }}
                        </a>
                    </li>

                    <li class="nav-item d-lg-none">
                        <form class="d-inline" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn">
                                <i class="fas fa-sign-out-alt fa-fw" aria-hidden="true"></i>
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </li>
                @else
                    <li class="nav-item {{ Route::current()->getName() === 'login' ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-user fa-fw" aria-hidden="true"></i>
                            {{ __('Login') }}
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
