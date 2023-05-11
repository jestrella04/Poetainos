<div class="d-flex justify-content-center loading-content">
    <div class="spinner-border infinite-scroll-request" role="status" title="{{ __('Loading...') }}">
        <span class="visually-hidden">{{ __('Loading...') }}</span>
    </div>

    <p class="infinite-scroll-last">{{ __('It\'s a shame, we have no more content to show.') }}</p>
    <p class="infinite-scroll-error">{{ __('It\'s a shame, we can\'t show more content at the moment.') }}</p>
</div>

<div class="d-flex justify-content-center">
    <button id="pagination-load-more" class="btn btn-secondary">{{ __('Load More') }}</button>
</div>
