@php
    $count = getWritingCounter($writing);
@endphp

<form class="stats writing-stats">
    <button class="btn btn-counter btn-sm" title="{{ __('Likes') }}" @guest disabled @endguest>
        <i class="fa fa-thumbs-up fa-fw"></i>
        <span class="counter">{{ $count['upvotes'] }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Dislikes') }}" @guest disabled @endguest>
        <i class="fa fa-thumbs-down fa-fw"></i>
        <span class="counter">{{ $count['downvotes'] }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Comments') }}" @guest disabled @endguest>
        <i class="fa fa-comment fa-fw"></i>
        <span class="counter">{{ $count['comments'] + $count['replies'] }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Views') }}" @guest disabled @endguest>
        <i class="fa fa-eye fa-fw"></i>
        <span class="counter">{{ $count['views'] }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Shelved') }}" @guest disabled @endguest>
        <i class="fa fa-book-reader fa-fw"></i>
        <span class="counter">{{ $count['shelf'] }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Aura') }}" @guest disabled @endguest>
        <i class="fa fa-dove fa-fw"></i>
        <span class="counter">{{ $count['aura'] }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Share') }}">
        <i class="fa fa-share-alt fa-fw"></i>
    </button>

    @if (auth()->check() && (auth()->user()->can('update', $writing) || auth()->user()->can('delete', $writing)))
        <div class="dropdown d-inline">
            <button
                class="btn btn-counter btn-sm"
                role="button" id="dropdownMenuLink"
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
