<aside class="col-lg-2 admin-sidebar">
    <div id="admin-sidebar-main" class="">
        <ul class="list-unstyled">
            <li class="brand">
                <a href="{{ route('home') }}" class="stretched-link" title="{{ __('Return to the homepage') }}">
                    <img src="{{ mix('/static/images/logo-32.png') }}" width="32" height="32" alt="logo">
                </a>
            </li>

            <li>
                <a href="{{ route('admin.settings') }}" class="stretched-link">
                    <i class="fas fa-fw fa-box"></i>
                    {{ __('Settings') }}
                </a>
            </li>

            <li>
                <a href="{{ route('admin.types') }}" class="stretched-link">
                    <i class="fas fa-fw fa-box"></i>
                    {{ __('Types') }}
                </a>
            </li>

            <li>
                <a href="{{ route('admin.categories') }}" class="stretched-link">
                    <i class="fas fa-fw fa-archive"></i>
                    {{ __('Categories') }}
                </a>
            </li>

            <li>
                <a href="{{ route('admin.pages') }}" class="stretched-link">
                    <i class="far fa-fw fa-file"></i>
                    {{ __('Pages') }}
                </a>
            </li>

            <li>
                <a href="{{ route('admin.users') }}" class="stretched-link">
                    <i class="fas fa-fw fa-users"></i>
                    {{ __('Users') }}
                </a>
            </li>
        </ul>
    </div>
</aside>
