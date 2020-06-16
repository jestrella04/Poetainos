<div class="block">
    <div class="block-title">
        {{ __('Featured Authors') }}
    </div>

    <div class="block-body">
        @forelse (App\User::featured(20) as $author)
        <div class="d-inline-flex d-lg-block author-link">
            <a href="{{ $author->path() }}">
                <div class="d-inline-flex">
                    <div>
                        @if (! empty($author->avatarPath()))
                            <img class="avatar" src="{{ $author->avatarPath() }}" title="{{ $author->getName() }}" alt="" loading="lazy">
                        @else
                            <span class="avatar" title="{{ $author->getName() }}">{{ $author->initials() }}</span>
                        @endif
                    </div>

                    <div>
                        <span>{{ $author->getName() }}</span>
                    </div>
                </div>
            </a>
        </div>

        @empty
            @include('partials.empty-block')
        @endforelse
    </div>
</div>
