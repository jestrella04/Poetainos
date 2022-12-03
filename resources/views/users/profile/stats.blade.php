@php
    $count = getUserCounter($user);
@endphp

<div class="d-flex stats user-stats">
    <span
        class="badge flex-fill"
        title="{{ __(':count Writings', ['count' => $count['writings']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-feather" aria-hidden="true"></i>
        <span class="counter">{{ $count['writings']['readable'] }}</span>
    </span>

    <span
        class="badge flex-fill"
        title="{{ __(':count Golden Flowers', ['count' => $count['flowers']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-fan" aria-hidden="true"></i>
        <span class="counter">{{ $count['flowers']['readable'] }}</span>
    </span>

    <span
        class="badge flex-fill"
        title="{{ __(':count Comments', ['count' => $count['comments']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-comment" aria-hidden="true"></i>
        <span class="counter">{{ $count['comments']['readable'] }}</span>
    </span>

    <span
        class="badge flex-fill"
        title="{{ __(':count Likes', ['count' => $count['votes']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fas fa-heart" aria-hidden="true"></i>
        <span class="counter">{{ $count['votes']['readable'] }}</span>
    </span>

    <span
        class="badge flex-fill"
        title="{{ __(':count Profile views', ['count' => $count['views']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-eye" aria-hidden="true"></i>
        <span class="counter">{{ $count['views']['readable'] }}</span>
    </span>

    @if (!($userList ?? false))
        <span
            class="badge flex-fill"
            title="{{ __(':count Shelved writings', ['count' => $count['shelf']['counter']]) }}"
            data-bs-toggle="tooltip"
            data-bs-placement="top">
            <i class="fa fa-bookmark" aria-hidden="true"></i>
            <span class="counter">{{ $count['shelf']['readable'] }}</span>
        </span>

        <span
            class="badge flex-fill"
            title="{{ __('Aura: :aura', ['aura' => $count['aura']['counter']]) }}"
            data-bs-toggle="tooltip"
            data-bs-placement="top">
            <i class="fas fa-dove" aria-hidden="true"></i>
            <span class="counter">{{ $count['aura']['readable'] }}</span>
        </span>
    @endif

    {{-- <span
            class="badge flex-fill"
            title="{{ __('User hood') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="top">
        <i class="fa fa-user-friends" aria-hidden="true"></i>
        <span class="counter">{{ $count['hood'] }}</span>
    </span>

    <span
        class="badge flex-fill"
        title="{{ __('Extended hood') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-users" aria-hidden="true"></i>
        <span class="counter">{{ $count['extendedHood'] }}</span>
    </span> --}}
</div>
