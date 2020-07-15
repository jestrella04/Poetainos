<div class="block">
    <div class="block-title">
        {{ __('Main categories') }}
    </div>

    <div class="block-body">
        @forelse (App\Category::main() as $category)
            <a href="{{ $category->path() }}" class="btn btn-outline-dark btn-sm writing-category d-title">
                <span>{{ $category->name }}</span>
                <span>({{ $category->writings_count }})</span>
            </a>
        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
