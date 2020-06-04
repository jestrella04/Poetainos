@php
    $count = getWritingCounter($writing);

    if (auth()->check()) {
        $userId = auth()->user()->id;
        $voted = App\Vote::where('user_id', $userId)->where('writing_id', $writing->id)->value('vote');
        $shelved = App\Shelf::where('user_id', $userId)->where('writing_id', $writing->id)->value('writing_id');
    }
@endphp

<div class="stats writing-stats">
    <button class="btn btn-sm btn-counter @auth {{ 'click like' }} @endauth @if (isset($voted) && $voted > 0) {{ 'voted' }} @endif"
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
        @endif>
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
        data-id="{{ $writing->id }}"
        data-value="0"
        @endif>
        <i class="fa fa-heart-broken fa-fw"></i>
        <span class="counter">{{ $count['downvotes'] }}</span>
    </button> --}}

    <button class="btn btn-sm btn-counter" title="{{ __('Comments') }}">
        <i class="fa fa-comment fa-fw"></i>
        <span class="counter">{{ $count['comments'] + $count['replies'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Views') }}">
        <i class="fa fa-eye fa-fw"></i>
        <span class="counter">{{ $count['views'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter @auth {{ 'click shelf' }} @endauth @if (isset($shelved) && $shelved === $writing->id) {{ 'shelved' }} @endif"
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
        @endif>
        <i class="fa fa-book-reader fa-fw"></i>
        <span class="counter">{{ $count['shelf'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Aura') }}">
        <i class="fa fa-dove fa-fw"></i>
        <span class="counter">{{ $count['aura'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter click share" title="{{ __('Share') }}">
        <i class="fa fa-share-alt fa-fw"></i>
    </button>

    @if (auth()->check() && (auth()->user()->can('update', $writing) || auth()->user()->can('delete', $writing)))
        <div class="dropdown d-inline">
            <button
                class="btn btn-sm btn-counter click owner"
                role="button"
                id="dropdownMenuLink"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-fw"></i>
            </button>

            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                @can('update', $writing)
                    <a class="dropdown-item" href="{{ route('writings.update', $writing) }}">{{ __('Edit') }}</a>
                @endcan

                @can('delete', $writing)
                    <a class="dropdown-item disabled" href="#">{{ __('Delete') }}</a>
                @endcan
            </div>
        </div>
    @endif
</div>
