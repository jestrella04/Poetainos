<aside id="admin-side-menu" class="side-menu">
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="fas fa-fw fa-feather-alt"></i>
                {{ __('Home') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.index') }}">
                <i class="fas fa-fw fa-home"></i>
                {{ __('Summary') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.settings') }}">
                <i class="fas fa-fw fa-cogs"></i>
                {{ __('Settings') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.types') }}">
                <i class="fas fa-fw fa-box"></i>
                {{ __('Types') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.categories') }}">
                <i class="fas fa-fw fa-archive"></i>
                {{ __('Categories') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.tags') }}">
                <i class="fas fa-fw fa-hashtag"></i>
                {{ __('Tags') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.pages') }}">
                <i class="far fa-fw fa-file"></i>
                {{ __('Pages') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.users') }}">
                <i class="fas fa-fw fa-users"></i>
                {{ __('Users') }}
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.writings') }}">
                <i class="fas fa-fw fa-feather"></i>
                {{ __('Writings') }}
            </a>
        </li>
    </ul>
</aside>
