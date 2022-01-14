@php
    $count = getUserCounter($user);
@endphp

<div class="d-flex flex-wrap justify-content-evenly stats user-stats">
    <span
        class="badge"
        title="{{ __('Writings') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-feather fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['writings'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __('Golden Flowers') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-fan fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['flowers'] }}</span>
    </span>

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
        title="{{ __('Likes') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-heart fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['votes']   }}</span>
    </span>

    <span
        class="badge"
        title="{{ __('Profile views') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-eye fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['views']  }}</span>
    </span>

    <span
        class="badge"
        title="{{ __('Shelved writings') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-bookmark fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['shelf']  }}</span>
    </span>

    {{-- <span
            class="badge"
            title="{{ __('User hood') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="top">
        <i class="fa fa-user-friends fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['hood'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __('Extended hood') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-users fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['extendedHood'] }}</span>
    </span> --}}

    <span
        class="badge"
        title="{{ __('Aura') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-dove fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['aura'] }}</span>
    </span>
</div>
