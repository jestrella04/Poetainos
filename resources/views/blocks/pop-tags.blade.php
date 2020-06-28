<div class="block">
    <div class="block-title">
        {{ __('Popular Tags') }}
    </div>

    <div class="block-body">
        @forelse (App\Tag::popular(20) as $tag)
            @if ($tag->writings_count > 0)
                <a href="{{ $tag->path() }}" class="btn btn-outline-dark btn-sm writing-tag d-title">
                    <span>{{ $tag->name }}</span>
                    <span>({{ $tag->writings_count }})</span>
                </a>
            @endif
        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
