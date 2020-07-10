@php $sort = request('sort') ?? 'featured' @endphp

<div id="users-top-links" class="top-links">
    <nav class="nav justify-content-center">
        <a class="{{ 'featured' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=featured">{{ __('Featured') }}</a>
        <a class="{{ 'latest' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=latest">{{ __('Latest') }}</a>
        <a class="{{ 'popular' === $sort ? 'active' : '' }}" href="{{ url()->current() }}?sort=popular">{{ __('Popular') }}</a>
    </nav>
</div>
