<div class="block">
    <h6 class="block-title">{{ __('Popular Categories') }}</h6>

    <div class="block-body">
        @forelse (App\Category::popular(20) as $category)
            <a href="{{ $category->path() }}" class="btn btn-outline-dark btn-sm writing-category">{{ $category->name }}</a>
        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
