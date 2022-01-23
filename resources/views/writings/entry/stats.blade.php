@php
    $count = getWritingCounter($writing);

    if (auth()->check()) {
        $userId = auth()->user()->id;
        $voted = App\Vote::where('user_id', $userId)->where('writing_id', $writing->id)->value('vote');
        $shelved = App\Shelf::where('user_id', $userId)->where('writing_id', $writing->id)->value('writing_id');
    }
@endphp

<div class="d-flex flex-wrap justify-content-evenly stats writing-stats">
    @if (isset($writing->home_posted_at))
        <span
            class="badge"
            title="{{ __('Awarded a Golden Flower') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="top">
            <i class="fas fa-fan fa-fw" style="color:goldenrod" aria-hidden="true"></i>
            <span class="visually-hidden">{{ __('Awarded a Golden Flower') }}</span>
        </span>
    @endif

    <span
        class="badge click like @if (isset($voted) && $voted > 0) {{ 'voted' }} @endif"
        @if (auth()->check() && $voted > 0)
        title="{{ __('Liked it') }}"
        @elseif (auth()->check() && null === $voted)
        title="{{ __('Like it') }}"
        @else
        title="{{ __('Likes') }}"
        @endif
        @if (auth()->check() && empty($vote))
        data-wh-target="{{ route('votes.store') }}"
        data-wh-id="{{ $writing->id }}"
        data-wh-value="1"
        @endif
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-heart fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['likes'] }}</span>
    </span>

    {{-- <span class="badge @auth {{ 'dislike' }} @endauth @if ($voted = 0) {{ 'voted' }} @endif"
        @if (auth()->check())
        title="{{ __('Dislike it') }}"
        @else
        title="{{ __('Dislikes') }}"
        @endif
        @if (auth()->check() && empty($vote))
        data-wh-target="{{ route('votes.store') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        data-wh-id="{{ $writing->id }}"
        data-wh-value="0"
        @endif>
        <i class="fa fa-heart-broken fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['downvotes'] }}</span>
    </span> --}}

    <span
        class="badge"
        title="{{ __('Comments') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-comment fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['comments'] + $count['replies'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __('Views') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-book-reader fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['views'] }}</span>
    </span>

    <span
        class="badge @if (isset($userId) && $userId !== $writing->author->id) {{ 'click shelf' }} @endif @if (isset($shelved) && $shelved === $writing->id) {{ 'shelved' }} @endif"
        @if (auth()->check() && $shelved === $writing->id)
        title="{{ __('On my shelf') }}"
        @elseif (auth()->check() && $userId !== $writing->author->id && null === $shelved)
        title="{{ __('Add to my shelf') }}"
        @else
        title="{{ __('Shelved') }}"
        @endif
        @if (auth()->check())
        data-wh-target="{{ route('shelves.store') }}"
        data-wh-id="{{ $writing->id }}"
        @endif
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-bookmark fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['shelf'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __('Aura') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-dove fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['aura'] }}</span>
    </span>

    <div class="dropdown dropdown-counter d-inline">
        <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Share writing') }}">
            <span
                class="badge click share"
                id="dropdown-share-{{ $writing->id }}"
                data-wh-url="{{ $writing->path() }}"
                data-wh-writing-title="{{ $writing->title }}"
                data-bs-toggle="dropdown"
                aria-label="{{ __('Share writing :writing', ['writing' => $writing->title]) }}"
                aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-share-alt fa-fw" aria-hidden="true"></i>
            </span>

            <div class="dropdown-menu" aria-labelledby="dropdown-share-{{ $writing->id }}">
                @foreach ($writing->shareLinks() as $serviceName => $serviceData)
                    <a class="dropdown-item {{ $serviceData['class'] }}"
                        href="{{ $serviceData['url'] }}"
                        rel="noopener">
                        <i class="{{ $serviceData['icon']}}" aria-hidden="true"></i>
                        {{ ucfirst($serviceName) }}
                    </a>
                @endforeach
            </div>
        </span>

    </div>

    @if (auth()->check() && (auth()->user()->can('update', $writing) || auth()->user()->can('delete', $writing)))
        <div class="dropdown dropdown-counter d-inline">
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Actions') }}">
                <span
                    class="badge click owner"
                    id="dropdown-owner"
                    data-bs-toggle="dropdown"
                    aria-label="{{ __('Actions') }}"
                    aria-haspopup="true"
                    aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-fw" aria-hidden="true"></i>
                </span>

                <div class="dropdown-menu" aria-labelledby="dropdown-owner">
                    <a class="dropdown-item" href="{{ route('writings.edit', $writing) }}">{{ __('Edit / Delete') }}</a>
                </div>
            </span>
        </div>
    @endif
</div>
