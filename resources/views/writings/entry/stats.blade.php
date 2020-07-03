@php
    $count = getWritingCounter($writing);

    if (auth()->check()) {
        $userId = auth()->user()->id;
        $voted = App\Vote::where('user_id', $userId)->where('writing_id', $writing->id)->value('vote');
        $shelved = App\Shelf::where('user_id', $userId)->where('writing_id', $writing->id)->value('writing_id');
    }
@endphp

<div class="stats writing-stats">
    <button
        class="btn btn-sm btn-counter @auth {{ 'click like' }} @endauth @if (isset($voted) && $voted > 0) {{ 'voted' }} @endif"
        @if (auth()->check() && $voted > 0)
        title="{{ __('Liked it') }}"
        @elseif (auth()->check() && null === $voted)
        title="{{ __('Like it') }}"
        @else
        title="{{ __('Likes') }}"
        @endif
        @if (auth()->check() && empty($vote))
        data-target="{{ route('votes.store') }}"
        data-id="{{ $writing->id }}"
        data-value="1"
        @endif
        data-toggle="tooltip"
        data-placement="top">
        <i class="fa fa-heart fa-fw"></i>
        <span class="counter">{{ $count['likes'] }}</span>
    </button>

    {{-- <button class="btn btn-sm btn-counter @auth {{ 'dislike' }} @endauth @if ($voted = 0) {{ 'voted' }} @endif"
        @if (auth()->check())
        title="{{ __('Dislike it') }}"
        @else
        title="{{ __('Dislikes') }}"
        @endif
        @if (auth()->check() && empty($vote))
        data-target="{{ route('votes.store') }}"
        data-toggle="tooltip"
        data-placement="top"
        data-id="{{ $writing->id }}"
        data-value="0"
        @endif>
        <i class="fa fa-heart-broken fa-fw"></i>
        <span class="counter">{{ $count['downvotes'] }}</span>
    </button> --}}

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Comments') }}"
        data-toggle="tooltip"
        data-placement="top">
        <i class="fa fa-comment fa-fw"></i>
        <span class="counter">{{ $count['comments'] + $count['replies'] }}</span>
    </button>

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Views') }}"
        data-toggle="tooltip"
        data-placement="top">
        <i class="fa fa-eye fa-fw"></i>
        <span class="counter">{{ $count['views'] }}</span>
    </button>

    <button
        class="btn btn-sm btn-counter @auth {{ 'click shelf' }} @endauth @if (isset($shelved) && $shelved === $writing->id) {{ 'shelved' }} @endif"
        @if (auth()->check() && $shelved === $writing->id)
        title="{{ __('On my shelf') }}"
        @elseif (auth()->check() && null === $shelved)
        title="{{ __('Add to my shelf') }}"
        @else
        title="{{ __('Shelved') }}"
        @endif
        @if (auth()->check())
        data-target="{{ route('shelves.store') }}"
        data-id="{{ $writing->id }}"
        @endif
        data-toggle="tooltip"
        data-placement="top">
        <i class="fa fa-book-reader fa-fw"></i>
        <span class="counter">{{ $count['shelf'] }}</span>
    </button>

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Aura') }}"
        data-toggle="tooltip"
        data-placement="top">
        <i class="fa fa-dove fa-fw"></i>
        <span class="counter">{{ $count['aura'] }}</span>
    </button>

    <div class="dropdown dropdown-counter d-inline">
        <button
            class="btn btn-sm btn-counter click share"
            title="{{ __('Share') }}"
            role="button"
            id="dropdown-share"
            data-url="{{ $writing->path() }}"
            data-writing-title="{{ $writing->title }}"
            data-toggle="tooltip"
            data-placement="top"
            aria-label="{{ __('Share') }}"
            aria-haspopup="true"
            aria-expanded="false">
            <i class="fa fa-share-alt fa-fw"></i>
        </button>

        <div class="dropdown-menu" aria-labelledby="dropdown-share">
            @foreach ($writing->shareLinks() as $serviceName => $serviceData)
                <a class="dropdown-item {{ $serviceData['class'] }}"
                    href="{{ $serviceData['url'] }}"
                    rel="noopener">
                    <i class="{{ $serviceData['icon']}}"></i>
                    {{ ucfirst($serviceName) }}
                </a>
            @endforeach
        </div>
    </div>

    @if (auth()->check() && (auth()->user()->can('update', $writing) || auth()->user()->can('delete', $writing)))
        <div class="dropdown dropdown-counter d-inline">
            <button
                class="btn btn-sm btn-counter click owner"
                role="button"
                id="dropdown-owner"
                data-toggle="dropdown"
                data-toggle="tooltip"
                data-placement="top"
                aria-label="{{ __('Edit / Delete') }}"
                aria-haspopup="true"
                aria-expanded="false">
                <i class="fas fa-cog fa-fw"></i>
            </button>

            <div class="dropdown-menu" aria-labelledby="dropdown-owner">
                <a class="dropdown-item" href="{{ route('writings.edit', $writing) }}">{{ __('Edit / Delete') }}</a>
            </div>
        </div>
    @endif
</div>
