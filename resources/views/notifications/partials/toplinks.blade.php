@php $filter = request('filter') ?? 'unread' @endphp

<div id="notification-top-links" class="top-links">
    <nav class="nav nav-pills justify-content-center">
        <a class="nav-item nav-link {{ 'unread' === $filter ? 'active' : '' }}" href="{{ url()->current() }}?filter=unread">{{ __('Unread') }}</a>
        <a class="nav-item nav-link {{ 'all' === $filter ? 'active' : '' }}" href="{{ url()->current() }}?filter=all">{{ __('All') }}</a>
    </nav>
</div>
