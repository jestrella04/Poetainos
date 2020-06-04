@php
    $count = getUserCounter($user);
@endphp

<div class="stats user-stats">
    <button class="btn btn-sm btn-counter" title="{{ __('Writings') }}">
        <i class="fa fa-feather fa-fw"></i>
        <span class="counter">{{ $count['writings'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Comments') }}">
        <i class="fa fa-comment fa-fw"></i>
        <span class="counter">{{ $count['comments'] + $count['replies'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Likes') }}">
        <i class="fas fa-heart fa-fw"></i>
        <span class="counter">{{ $count['votes']   }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Profile views') }}">
        <i class="fa fa-eye fa-fw"></i>
        <span class="counter">{{ $count['views']  }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Shelved writings') }}">
        <i class="fa fa-book-reader fa-fw"></i>
        <span class="counter">{{ $count['shelf']  }}</span>
    </button>

    {{-- <button class="btn btn-sm btn-counter" title="{{ __('User hood') }}">
        <i class="fa fa-user-friends fa-fw"></i>
        <span class="counter">{{ $count['hood'] }}</span>
    </button>

    <button class="btn btn-sm btn-counter" title="{{ __('Extended hood') }}">
        <i class="fa fa-users fa-fw"></i>
        <span class="counter">{{ $count['extendedHood'] }}</span>
    </button> --}}

    <button class="btn btn-sm btn-counter" title="{{ __('Aura') }}">
        <i class="fas fa-dove fa-fw"></i>
        <span class="counter">{{ $count['aura'] }}</span>
    </button>
</div>
