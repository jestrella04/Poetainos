<div class="block">
    <div class="block-title">
        {{ __('Popular Categories') }}
    </div>

    <div class="block-body">
        @forelse (App\Category::popular(20) as $category)
            @if ($category->writings_count > 0)
                <a href="{{ $category->path() }}" class="btn btn-outline-dark btn-sm writing-category d-title">
                    <span>{{ $category->name }}</span>
                    <span>({{ $category->writings_count }})</span>
                </a>
            @endif
        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
