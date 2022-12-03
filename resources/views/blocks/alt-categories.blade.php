<div class="block">
    <div class="block-title">
        {{ __('Alternative categories') }}
    </div>

    <div class="block-body">
        @forelse (App\Models\Category::secondary() as $category)
            <a href="{{ $category->path() }}" class="btn btn-outline-primary btn-sm writing-category d-title">
                <span>{{ $category->name }}</span>
                <span>({{ $category->writingsRecursive()->count() }})</span>
            </a>
        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
