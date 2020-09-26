<div id="writings-top-links" class="top-links">
    <nav class="nav nav-pills justify-content-center">
        <a class="nav-item nav-link {{ 'latest' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=latest">{{ __('Most recent') }}</a>
        <a class="nav-item nav-link {{ 'popular' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=popular">{{ __('Most popular') }}</a>
        <a class="nav-item nav-link {{ 'likes' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=likes">{{ __('Most liked') }}</a>
    </nav>
</div>
