<div class="block">
    <h6 class="block-title">{{ __('Featured Authors') }}</h6>

    <div class="block-body">
        @forelse (getFeaturedAuthors(20) as $author)
        <div class="d-inline-flex d-lg-block author-link">
            <a href="{{ $author->path() }}">
                <div class="d-inline-flex">
                    <div>
                        @if (! empty($author->avatarPath()))
                            <img class="avatar" src="{{ $author->avatarPath() }}" title="{{ $author->fullName() }}" alt="" loading="lazy">
                        @else
                            <span class="avatar" title="{{ $author->fullName() }}">{{ $author->initials() }}</span>
                        @endif
                    </div>

                    <div>
                        <span>{{ $author->fullName() }}</span>
                    </div>
                </div>
            </a>
        </div>

        @empty
            <p class="text-muted">{{ __('Give me the authors!') }}</p>
        @endforelse
    </div>
</div>
