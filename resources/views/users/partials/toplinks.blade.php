<div id="users-top-links" class="top-links">
    <div class="nav nav-tabs nav-fill">
        <a @class([
            'nav-item',
            'nav-link',
            'active' => 'featured' === $sort,
        ]) href="{{ url()->current() }}?sort=featured"
        title="{{ __('Featured') }}">
            <i class="fa-solid fa-fan" aria-hidden="true"></i>
            <span class="d-none d-md-inline-block">{{ __('Featured') }}</span>
        </a>

        <a @class([
            'nav-item',
            'nav-link',
            'active' => 'latest' === $sort,
        ]) href="{{ url()->current() }}?sort=latest"
        title="{{ __('Latest') }}">
            <i class="fa-solid fa-clock" aria-hidden="true"></i>
            <span class="d-none d-md-inline-block">{{ __('Latest') }}</span>
        </a>

        <a @class([
            'nav-item',
            'nav-link',
            'active' => 'popular' === $sort,
        ]) href="{{ url()->current() }}?sort=likes"
        title="{{ __('Popular') }}">
            <i class="fa-solid fa-fire-flame-curved" aria-hidden="true"></i>
            <span class="d-none d-md-inline-block">{{ __('Popular') }}</span>
        </a>
    </div>
</div>
