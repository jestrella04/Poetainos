<div class="block">
    <h6 class="block-title">{{ __('Popular Tags') }}</h6>

    <div class="block-body">
        @forelse (getPopularTags(20) as $tag)
            <a href="{{ $tag->path() }}" class="btn btn-outline-dark btn-sm writing-tag">{{ $tag->name }}</a>
        @empty
            <p class="text-muted">{{ __('Give me the tags!') }}</p>
        @endforelse
    </div>
</div>
