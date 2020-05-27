<div class="stats user-stats">
    <button class="btn btn-light btn-sm" title="{{ __('Writings') }}">
        <i class="fa fa-feather fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($user->writings()->count()) }}</span>
    </button>

    <button class="btn btn-light btn-sm" title="{{ __('Comments') }}">
        <i class="fa fa-comment fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($user->comments()->count()) }}</span>
    </button>

    <button class="btn btn-light btn-sm" title="{{ __('Votes') }}">
        <i class="fas fa-thumbs-up fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($user->votes()->count())  }}</span>
    </button>

    <button class="btn btn-light btn-sm" title="{{ __('Profile views') }}">
        <i class="fa fa-eye fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($user->profile_views ) }}</span>
    </button>

    <button class="btn btn-light btn-sm" title="{{ __('Shelved writings') }}">
        <i class="fa fa-book-reader fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($user->shelf()->count()) }}</span>
    </button>

    <button class="btn btn-light btn-sm" title="{{ __('User hood') }}">
        <i class="fa fa-user-friends fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($user->hood()->count()) }}</span>
    </button>

    <button class="btn btn-light btn-sm" title="{{ __('Extended hood') }}">
        <i class="fa fa-users fa-fw"></i>
        <span class="counter">{{ ReadableHumanNumber($user->fellowHood($count = true)) }}</span>
    </button>

    <button class="btn btn-light btn-sm" title="{{ __('Aura') }}">
        <i class="fas fa-dove fa-fw"></i>
        <span class="counter">{{ $user->aura  }}</span>
    </button>
</div>
