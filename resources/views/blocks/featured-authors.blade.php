<div class="block">
    <div class="block-title">
        {{ __('Featured Authors') }}
    </div>

    <div class="block-body">
        @forelse (App\User::featured(20) as $author)
        <div class="d-inline-flex d-lg-block author-link">
            <a href="{{ $author->path() }}" class="stretched-link">
                <div class="d-inline-flex">
                    <div>{!! getUserAvatar($author) !!}</div>
                    <div>{{ $author->getName() }}</div>
                </div>
            </a>
        </div>

        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
