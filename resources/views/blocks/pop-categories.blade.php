<div class="block">
    <h6 class="block-title">{{ __('Popular Categories') }}</h6>

    <div class="block-body">
        @forelse (getPopularCategories(10) as $category)
            <a href="{{ $category->path() }}" class="btn btn-outline-dark btn-sm writing-category">{{ $category->name }}</a>
        @empty
            <p class="text-muted">{{ __('Give me the categories!') }}</p>
        @endforelse
    </div>
</div>
