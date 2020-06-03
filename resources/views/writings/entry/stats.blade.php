@php
    $count = getWritingCounter($writing);
    if (auth()->check()) {
        $voted = App\Vote::where('user_id', auth()->user()->id)->where('writing_id', $writing->id)->value('vote');
    } else {
        $voted = -1;
    }
@endphp

<form class="stats writing-stats">
    <button class="btn btn-sm btn-counter @auth {{ 'like' }} @endauth @if ($voted > 0) {{ 'voted' }} @endif"
        title="{{ __('Likes') }}"
        @if (auth()->check() && empty($vote))
        data-target="{{ route('votes.store') }}"
        data-id="{{ $writing->id }}"
        data-value="1"
        @endif>
        <i class="fa fa-heart fa-fw"></i>
        <span class="counter">{{ $count['upvotes'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter @auth {{ 'dislike' }} @endauth @if ($voted = 0) {{ 'voted' }} @endif"
        title="{{ __('Dislikes') }}"
        @if (auth()->check() && empty($vote))
        data-target="{{ route('votes.store') }}"
        data-id="{{ $writing->id }}"
        data-value="0"
        @endif>
        <i class="fa fa-heart-broken fa-fw"></i>
        <span class="counter">{{ $count['downvotes'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Comments') }}">
        <i class="fa fa-comment fa-fw"></i>
        <span class="counter">{{ $count['comments'] + $count['replies'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Views') }}">
        <i class="fa fa-eye fa-fw"></i>
        <span class="counter">{{ $count['views'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter"
        title="{{ __('Shelved') }}"
        @auth
        data-target="{{ route('shelves.store') }}"
        data-id="{{ $writing->id }}"
        @endauth>
        <i class="fa fa-book-reader fa-fw"></i>
        <span class="counter">{{ $count['shelf'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Aura') }}">
        <i class="fa fa-dove fa-fw"></i>
        <span class="counter">{{ $count['aura'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter share" title="{{ __('Share') }}">
        <i class="fa fa-share-alt fa-fw"></i>
    </button>

    @if (auth()->check() && (auth()->user()->can('update', $writing) || auth()->user()->can('delete', $writing)))
        <div class="dropdown d-inline">
            <button
                class="btn btn-sm btn-counter owner"
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
</form>
