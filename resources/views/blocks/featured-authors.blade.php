<div class="block">
    <div class="block-title">
        {{ __('Featured Authors') }}
    </div>

    <div class="block-body">
        @forelse (App\User::featured(15) as $author)
        <div class="d-inline-flex author-link">
            <a href="{{ $author->path() }}" data-bs-toggle="tooltip" title="{{ $author->getName() }}">
                {!! getUserAvatar($author) !!}
            </a>
        </div>

        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
