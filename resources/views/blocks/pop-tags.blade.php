<div class="block">
    <div class="block-title">
        {{ __('Popular Tags') }}
    </div>

    <div class="block-body">
        @forelse (App\Tag::popular(20) as $tag)
            <a href="{{ $tag->path() }}" class="btn btn-outline-dark btn-sm writing-tag">{{ $tag->name }}</a>
        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
