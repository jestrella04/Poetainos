@php
    $count = getUserCounter($user);
@endphp

<div class="stats user-stats">
    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Writings') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-feather fa-fw"></i>
        <span class="counter">{{ $count['writings'] }}</span>
    </button>

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Golden Flowers') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-fan fa-fw"></i>
        <span class="counter">{{ $count['flowers'] }}</span>
    </button>

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Comments') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-comment fa-fw"></i>
        <span class="counter">{{ $count['comments'] + $count['replies'] }}</span>
    </button>

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Likes') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-heart fa-fw"></i>
        <span class="counter">{{ $count['votes']   }}</span>
    </button>

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Profile views') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-eye fa-fw"></i>
        <span class="counter">{{ $count['views']  }}</span>
    </button>

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Shelved writings') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-book-reader fa-fw"></i>
        <span class="counter">{{ $count['shelf']  }}</span>
    </button>

    {{-- <button
            class="btn btn-sm btn-counter"
            title="{{ __('User hood') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="top">
        <i class="fa fa-user-friends fa-fw"></i>
        <span class="counter">{{ $count['hood'] }}</span>
    </button>

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Extended hood') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-users fa-fw"></i>
        <span class="counter">{{ $count['extendedHood'] }}</span>
    </button> --}}

    <button
        class="btn btn-sm btn-counter"
        title="{{ __('Aura') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-dove fa-fw"></i>
        <span class="counter">{{ $count['aura'] }}</span>
    </button>
</div>
