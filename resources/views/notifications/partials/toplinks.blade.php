<nav class="nav nav-tabs nav-fill top-links mb-3">
    <a @class([
        'nav-link',
        'active' => Route::current()->getName() === 'notifications.list.unread',
    ]) href="{{ route('notifications.list.unread') }}"
    title="{{ __('Unread') }}">
        {{ __('Unread') }}
    </a>

    <a @class([
        'nav-link',
        'active' => Route::current()->getName() ==='notifications.list.all',
    ]) href="{{ route('notifications.list.all') }}"
    title="{{ __('All') }}">
        {{ __('All') }}
    </a>
</nav>
