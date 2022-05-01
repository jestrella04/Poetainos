@php $filter = request('filter') ?? 'unread' @endphp

<div id="notification-top-links" class="top-links">
    <div class="nav nav-tabs nav-fill">
        <a @class([
            'nav-item',
            'nav-link',
            'active' => 'unread' === $filter,
        ]) href="{{ url()->current() }}?filter=unread"
        title="{{ __('Unread') }}">
            {{ __('Unread') }}
        </a>

        <a @class([
            'nav-item',
            'nav-link',
            'active' => 'all' === $filter,
        ]) href="{{ url()->current() }}?filter=all"
        title="{{ __('All') }}">
            {{ __('All') }}
        </a>
    </div>
</div>
