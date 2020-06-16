<div class="block">
    <div class="block-title">
        {{ __('Popular Categories') }}
    </div>

    <div class="block-body">
        @forelse (App\Category::popular(20) as $category)
            <a href="{{ $category->path() }}" class="btn btn-outline-dark btn-sm writing-category">{{ $category->name }}</a>
        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
