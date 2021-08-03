<div id="users-top-links" class="top-links">
    <div class="nav nav-pills justify-content-center">
        <a class="nav-item nav-link {{ 'featured' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=featured">{{ __('Featured') }}</a>
        <a class="nav-item nav-link {{ 'latest' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=latest">{{ __('Latest') }}</a>
        <a class="nav-item nav-link {{ 'popular' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=popular">{{ __('Popular') }}</a>
    </div>
</div>
