<div id="writings-top-links" class="top-links">
    <nav class="nav nav-pills justify-content-center">
        <a class="nav-item nav-link {{ 'latest' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=latest">{{ __('Latest') }}</a>
        <a class="nav-item nav-link {{ 'popular' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=popular">{{ __('Popular') }}</a>
        @if ($main ?? false)
        <a class="nav-item nav-link {{ 'lobby' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=lobby">{{ __('Lobby') }}</a>
        @endif
    </nav>
</div>
