<nav id="nav-main" class="navbar navbar-dark navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ mix('/static/images/logo.svg') }}" width="32" height="32" alt="{{ getSiteConfig('name') }}">
        </a>

        <div class="toggler-wrapper">
            <button
                id="toggler"
                class="rounded navbar-toggler"
                data-source="#header-navbar"
                data-target="#user-side-menu"
                aria-label="{{ __('Toggle sidebar') }}">
                <span class="icon-badge-container">
                    <i class="fa fa-bars"></i>

                    @if (auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                        <div class="icon-badge"></div>
                    @endif
                </span>
            </button>
        </div>

        <div id="header-navbar" class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-lg-none">
                    <a class="nav-link" href="{{ route('home') }}">
                        <i class="fas fa-home fa-fw"></i>
                        {{ __('Home') }}
                    </a>
                </li>

                @auth
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ route('notifications.index') }}">
                            <i class="fas fa-bell fa-fw"></i>
                            {{ __('Notifications') }}

                            @if (auth()->user()->unreadNotifications->count() > 0)
                                <span class="badge badge-light ml-3">{{ auth()->user()->unreadNotifications->count() }}</span>
                                <span class="sr-only">{{ __('Unread notifications') }}</span>
                            @endif
                        </a>
                    </li>
                @endauth

                @if (auth()->check() && auth()->user()->isAllowed('admin'))
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ route('admin.index') }}">
                            <i class="fas fa-cogs fa-fw"></i>
                            {{ __('Administration') }}
                        </a>
                    </li>
                @endif

                <li class="nav-item {{ Route::current()->getName() === 'users.index' ? 'active' : '' }}">
                    <a class="nav-link " href="{{ route('users.index') }}">
                        <i class="fas fa-users fa-fw"></i>
                        {{ __('Writers') }}
                    </a>
                </li>

                <li class="nav-item {{ Route::current()->getName() === 'writings.index' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('writings.index') }}">
                        <i class="fas fa-feather fa-fw"></i>
                        {{ __('Writings') }}
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('writings.random') }}">
                        <i class="fas fa-random fa-fw"></i>
                        {{ __('Random') }}
                    </a>
                </li>

                <li class="nav-item {{ Route::current()->getName() === 'writings.create' ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('writings.create') }}">
                        <i class="fas fa-pen-nib fa-fw"></i>
                        {{ __('Publish') }}
                    </a>
                </li>

                {{-- <li class="nav-item {{ Route::current()->getName() === 'pages' ? 'active' : '' }}">
                    <a class="nav-link" href="/page/about">
                        <i class="fas fa-info-circle fa-fw"></i>
                        {{ __('About') }}
                    </a>
                </li> --}}

                @auth
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="icon-badge-container">
                                {!! getUserAvatar(auth()->user()) !!}
                                @if (auth()->user()->unreadNotifications->count() > 0)
                                    <div class="icon-badge"></div>
                                @endif
                            </div>

                            {{ __('Hi') }} {{ auth()->user()->firstName() }}
                        </a>

                        <div class="dropdown-menu">
                            @if (auth()->user()->isAllowed('admin'))
                                <a class="dropdown-item" href="{{ route('admin.index') }}">{{ __('Administration') }}</a>
                            @endif
                            <a class="dropdown-item" href="{{ route('notifications.index') }}">
                                {{ __('Notifications') }}

                                @if (auth()->user()->unreadNotifications->count() > 0)
                                    <span class="badge badge-dark ml-3">{{ auth()->user()->unreadNotifications->count() }}</span>
                                    <span class="sr-only">{{ __('Unread notifications') }}</span>
                                @endif
                            </a>
                            <a class="dropdown-item" href="{{ auth()->user()->path() }}">{{ __('My profile') }}</a>
                            <a class="dropdown-item" href="{{ auth()->user()->writingsPath() }}">{{ __('My writings') }}</a>
                            <a class="dropdown-item" href="{{ auth()->user()->shelfPath() }}">{{ __('My shelf') }}</a>
                            <form class="d-inline" method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn dropdown-item">{{ __('Logout') }}</button>
                            </form>
                        </div>
                    </li>

                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ auth()->user()->path() }}">
                            <i class="fas fa-user-circle fa-fw"></i>
                            {{ __('My profile') }}
                        </a>
                    </li>

                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ auth()->user()->writingsPath() }}">
                            <i class="fas fa-feather fa-fw"></i>
                            {{ __('My writings') }}
                        </a>
                    </li>

                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ auth()->user()->shelfPath() }}">
                            <i class="fas fa-book-reader fa-fw"></i>
                            {{ __('My shelf') }}
                        </a>
                    </li>

                    <li class="nav-item d-lg-none">
                        <form class="d-inline" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn">
                                <i class="fas fa-sign-out-alt fa-fw"></i>
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </li>
                @else
                    <li class="nav-item {{ Route::current()->getName() === 'login' ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-user fa-fw"></i>
                            {{ __('Login') }}
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
