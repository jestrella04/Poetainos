<div id="writings-top-links" class="top-links">
    <div class="nav nav-tabs nav-fill">
        <a @class([
            'nav-item',
            'nav-link',
            'active' => 'latest' === $sort,
        ]) href="{{ url()->current() }}?sort=latest"
        title="{{ __('Most recent') }}">
            <i class="fa-solid fa-clock" aria-hidden="true"></i>
            <span class="d-none d-md-inline-block">{{ __('Most recent') }}</span>
        </a>

        <a @class([
            'nav-item',
            'nav-link',
            'active' => 'popular' === $sort,
        ]) href="{{ url()->current() }}?sort=popular"
        title="{{ __('Most popular') }}">
            <i class="fa-solid fa-fire-flame-curved" aria-hidden="true"></i>
            <span class="d-none d-md-inline-block">{{ __('Most popular') }}</span>
        </a>

        <a @class([
            'nav-item',
            'nav-link',
            'active' => 'likes' === $sort,
        ]) href="{{ url()->current() }}?sort=likes"
        title="{{ __('Most liked') }}">
            <i class="fa-solid fa-heart" aria-hidden="true"></i>
            <span class="d-none d-md-inline-block">{{ __('Most liked') }}</span>
        </a>
    </div>
</div>
