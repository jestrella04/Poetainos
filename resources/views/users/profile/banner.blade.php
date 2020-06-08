@php
    if (isset($params['author'])) $user = $params['author']
@endphp

<div class="user-banner text-center">
    <a href="{{ $user->path() }}" class="avatar-link">
        @if (! empty($user->avatarPath()))
            <img class="avatar" src="{{ $user->avatarPath() }}" title="{{ $user->getName() }}" alt="" loading="lazy">
        @else
            <span class="avatar" title="{{ $user->getName() }}">{{ $user->initials() }}</span>
        @endif

        <h6 class="text-truncate">{{ $user->getName() }}</h6>

        {{-- @if (! empty($user->extra_info['bio']))
            <p>{{ $user->extra_info['bio'] }}</p>
        @endif --}}
    </a>
</div>
