<aside class="col-lg-2 admin-sidebar">
    <div class="admin-sidebar-toggle d-block d-lg-none">
        <a class="btn btn-dark btn-block rounded-0"
            data-toggle="collapse"
            href="#admin-sidebar-main"
            role="button"
            aria-label="{{ __('Toggle sidebar') }}">
            <i class="fa fa-chevron-down"></i>
        </a>
    </div>

    <div id="admin-sidebar-main" class="collapse no-collapse-lg">
        <ul class="list-unstyled">
            <li class="brand">
                <a href="{{ route('home') }}" class="stretched-link" title="{{ __('Return to the homepage') }}">
                    <img src="{{ mix('/static/images/logo-32.png') }}" width="32" height="32" alt="logo">
                </a>
            </li>

            <li>
                <a href="{{ route('admin.index') }}" class="stretched-link">
                    <i class="fas fa-fw fa-home"></i>
                    {{ __('Summary') }}
                </a>
            </li>

            <li>
                <a href="{{ route('admin.settings') }}" class="stretched-link">
                    <i class="fas fa-fw fa-cogs"></i>
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
                <a href="{{ route('admin.tags') }}" class="stretched-link">
                    <i class="fas fa-fw fa-hashtag"></i>
                    {{ __('Tags') }}
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
