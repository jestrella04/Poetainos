<nav id="nav-footer" class="navbar navbar-dark fixed-bottom bg-primary">
    <div class="container">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item d-lg-none">
                <a class="nav-link btn position-relative" href="{{ route('notifications.index') }}">
                    <i class="fas fa-bell fa-fw" aria-hidden="true"></i>

                    <span class="badge badge-indicator bg-light unread-count">
                        {{ auth()->user()->unreadNotifications->count() ?:'' }}
                    </span>

                    <span class="visually-hidden">{{ __('unread notifications') }}</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
