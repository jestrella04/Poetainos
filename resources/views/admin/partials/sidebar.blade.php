<aside id="admin-side-menu" class="side-menu">
    <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="fas fa-fw fa-feather-alt" aria-hidden="true"></i>
                {{ __('Home') }}
            </a>
        </li>

        <li class="nav-item {{ Route::current()->getName() === 'admin.index' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.index') }}">
                <i class="fas fa-fw fa-home" aria-hidden="true"></i>
                {{ __('Summary') }}
            </a>
        </li>

        <li class="nav-item {{ Route::current()->getName() === 'admin.settings' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.settings') }}">
                <i class="fas fa-fw fa-cogs" aria-hidden="true"></i>
                {{ __('Settings') }}
            </a>
        </li>

        <li class="nav-item {{ Route::current()->getName() === 'admin.categories' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.categories') }}">
                <i class="fas fa-fw fa-folder-open" aria-hidden="true"></i>
                {{ __('Categories') }}
            </a>
        </li>

        <li class="nav-item {{ Route::current()->getName() === 'admin.tags' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.tags') }}">
                <i class="fas fa-fw fa-hashtag" aria-hidden="true"></i>
                {{ __('Tags') }}
            </a>
        </li>

        <li class="nav-item {{ Route::current()->getName() === 'admin.pages' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.pages') }}">
                <i class="far fa-fw fa-file" aria-hidden="true"></i>
                {{ __('Pages') }}
            </a>
        </li>

        <li class="nav-item {{ Route::current()->getName() === 'admin.users' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.users') }}">
                <i class="fas fa-fw fa-users" aria-hidden="true"></i>
                {{ __('Users') }}
            </a>
        </li>

        <li class="nav-item {{ Route::current()->getName() === 'admin.writings' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.writings') }}">
                <i class="fas fa-fw fa-feather" aria-hidden="true"></i>
                {{ __('Writings') }}
            </a>
        </li>

        <li class="nav-item {{ Route::current()->getName() === 'admin.tools' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.tools') }}">
                <i class="fas fa-fw fa-tools" aria-hidden="true"></i>
                {{ __('Tools') }}
            </a>
        </li>
    </ul>
</aside>
