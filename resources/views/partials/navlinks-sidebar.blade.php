<ul class="navbar-nav ms-auto">
    @if (auth()->check() && auth()->user()->isAllowed('admin'))
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.index') }}">
                <i class="fas fa-cogs fa-fw" aria-hidden="true"></i>
                {{ __('Administration') }}
            </a>
        </li>
    @endif

    <li class="nav-item {{ Route::current()->getName() === 'home' ? 'active' : '' }}">
        <a class="nav-link " href="{{ route('home') }}">
            <i class="fas fa-home fa-fw" aria-hidden="true"></i>
            {{ __('Home') }}
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

    <li class="nav-item {{ Route::current()->getName() === 'writings.awards' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('writings.awards') }}">
            <i class="fas fa-fan fa-fw" aria-hidden="true"></i>
            {{ __('Golden Flowers') }}
        </a>
    </li>

    <li class="nav-item {{ Route::current()->getName() === 'users.index' ? 'active' : '' }}">
        <a class="nav-link " href="{{ route('users.index') }}">
            <i class="fas fa-users fa-fw" aria-hidden="true"></i>
            {{ __('Writers') }}
        </a>
    </li>

    @guest
        <li class="nav-item {{ Route::current()->getName() === 'login' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('socialite') }}">
                <i class="fas fa-user fa-fw" aria-hidden="true"></i>
                {{ __('Login') }}
            </a>
        </li>
    @endguest

    <li class="nav-item {{ Route::current()->getName() === 'contact.create' ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('contact.create') }}">
            <i class="fas fa-envelope fa-fw" aria-hidden="true"></i>
            {{ __('Contact us') }}
        </a>
    </li>
</ul>
