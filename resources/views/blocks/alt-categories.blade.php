<div class="block">
    <div class="block-title">
        {{ __('Alternative categories') }}
    </div>

    <div class="block-body">
        @forelse (App\Category::secondary() as $category)
            <a href="{{ $category->path() }}" class="btn btn-outline-dark btn-sm writing-category d-title">
                <span>{{ $category->name }}</span>
                <span>({{ $category->writingsCount() }})</span>
            </a>
        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
