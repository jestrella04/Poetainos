<div id="writings-top-links" class="top-links">
    <nav class="nav justify-content-center">
        <a class="{{ 'latest' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=latest">{{ __('Latest') }}</a>
        <a class="{{ 'popular' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=popular">{{ __('Popular') }}</a>
        @if ($main ?? false)
        <a class="{{ 'lobby' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=lobby">{{ __('Lobby') }}</a>
        @endif
    </nav>
</div>
