<ul class="navbar-nav ms-auto">
    @auth
        <li class="nav-item">
            <a class="nav-link " href="{{ auth()->user()->path() }}">
                <i class="fas fa-user fa-fw" aria-hidden="true"></i>
                {{ __('My profile') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link " href="{{ auth()->user()->writingsPath() }}">
                <i class="fas fa-feather fa-fw" aria-hidden="true"></i>
                {{ __('My writings') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link " href="{{ auth()->user()->shelfPath() }}">
                <i class="fas fa-book-bookmark fa-fw" aria-hidden="true"></i>
                {{ __('My shelf') }}
            </a>
        </li>

        @if (auth()->user()->isAllowed('admin'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.index') }}">
                    <i class="fas fa-cogs fa-fw" aria-hidden="true"></i>
                    {{ __('Administration') }}
                </a>
            </li>
        @endif

        <li class="nav-item">
            <form class="d-inline" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn dropdown-item">
                    <i class="fa-solid fa-right-from-bracket fa-fw" aria-hidden="true"></i>
                    {{ __('Logout') }}
                </button>
            </form>
        </li>
    @else
        <li class="nav-item {{ Route::current()->getName() === 'login' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('socialite') }}">
                <i class="fas fa-user fa-fw" aria-hidden="true"></i>
                {{ __('Login') }}
            </a>
        </li>
    @endauth

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

    <li class="nav-item {{ Route::current()->getName() === 'contact.create' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('contact.create') }}">
            <i class="fas fa-envelope fa-fw" aria-hidden="true"></i>
            {{ __('Contact us') }}
        </a>
    </li>
</ul>
