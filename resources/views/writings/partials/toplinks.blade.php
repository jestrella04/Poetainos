@php $sort = request('sort') ?? 'latest' @endphp

<div id="writings-top-links" class="top-links">
    <nav class="nav justify-content-center">
        <a class="{{ 'latest' === $sort ? 'active' : '' }}" href="?sort=latest">{{ __('Latest') }}</a>
        <a class="{{ 'popular' === $sort ? 'active' : '' }}" href="?sort=popular">{{ __('Popular') }}</a>
    </nav>
</div>
