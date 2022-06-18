@php
    $count = getUserCounter($user);
@endphp

<div class="d-flex flex-wrap justify-content-evenly stats user-stats">
    <span
        class="badge"
        title="{{ __(':count Writings', ['count' => $count['writings']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-feather fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['writings']['readable'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __(':count Golden Flowers', ['count' => $count['flowers']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-fan fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['flowers']['readable'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __(':count Comments', ['count' => $count['comments']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-comment fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['comments']['readable'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __(':count Likes', ['count' => $count['votes']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-heart fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['votes']['readable'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __(':count Profile views', ['count' => $count['views']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-eye fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['views']['readable'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __(':count Shelved writings', ['count' => $count['shelf']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-bookmark fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['shelf']['readable'] }}</span>
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
        title="{{ __('Aura: :aura', ['aura' => $count['aura']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-dove fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['aura']['readable'] }}</span>
    </span>
</div>
