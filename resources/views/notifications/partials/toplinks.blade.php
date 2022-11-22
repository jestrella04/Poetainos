<div id="notification-top-links" class="top-links">
    <div class="nav nav-tabs nav-fill">
        <a @class([
            'nav-item',
            'nav-link',
            'active' => Route::current()->getName() === 'notifications.list.unread',
        ]) href="{{ route('notifications.list.unread') }}"
        title="{{ __('Unread') }}">
            {{ __('Unread') }}
        </a>

        <a @class([
            'nav-item',
            'nav-link',
            'active' => Route::current()->getName() ==='notifications.list.all',
        ]) href="{{ route('notifications.list.all') }}"
        title="{{ __('All') }}">
            {{ __('All') }}
        </a>
    </div>
</div>
