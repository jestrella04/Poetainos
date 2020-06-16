@php
    if (isset($params['author'])) $user = $params['author']
@endphp

<div class="user-banner text-center">
    <a href="{{ $user->path() }}"
        class="avatar-link"
        title="{{ __('View profile') }}"
        data-toggle="tooltip"
        data-placement="top">
        @if (! empty($user->avatarPath()))
            <img class="avatar" src="{{ $user->avatarPath() }}" alt="" loading="lazy">
        @else
            <span class="avatar">
                {{ $user->initials() }}
            </span>
        @endif

        <div class="user-name text-truncate">
            {{ $user->getName() }}
        </div>
    </a>
</div>
