<form class="stats writing-stats">
    <button class="btn btn-counter btn-sm" title="{{ __('Likes') }}" @guest disabled @endguest>
        <i class="fa fa-thumbs-up fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($writing->votes->where('vote', '>', 0)->count()) }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Dislikes') }}" @guest disabled @endguest>
        <i class="fa fa-thumbs-down fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($writing->votes->where('vote', 0)->count()) }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Comments') }}" @guest disabled @endguest>
        <i class="fa fa-comment fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($writing->comments->count()) }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Views') }}" data-target="{{ $writing->path() }}" @guest disabled @endguest>
        <i class="fa fa-eye fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($writing->views) }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Shelved') }}" @guest disabled @endguest>
        <i class="fa fa-book-reader fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($writing->shelf->count()) }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Aura') }}" @guest disabled @endguest>
        <i class="fa fa-dove fa-fw"></i>
        <span class="counter">{{ $writing->aura }}</span>
    </button>

    <button class="btn btn-counter btn-sm" title="{{ __('Share') }}">
        <i class="fa fa-share-alt fa-fw"></i>
    </button>
</form>
