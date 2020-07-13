@php $filter = request('filter') ?? 'unread' @endphp

<div id="notification-top-links" class="top-links">
    <nav class="nav justify-content-center">
        <a class="{{ 'unread' === $filter ? 'active' : '' }}" href="{{ url()->current() }}?filter=unread">{{ __('Unread') }}</a>
        <a class="{{ 'all' === $filter ? 'active' : '' }}" href="{{ url()->current() }}?filter=all">{{ __('All') }}</a>
    </nav>
</div>
