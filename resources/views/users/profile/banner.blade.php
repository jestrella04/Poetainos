@php
    if (isset($params['author'])) $user = $params['author']
@endphp

<div class="user-banner text-center">
    <a href="{{ $user->path() }}" class="avatar-link">
        {!! getUserAvatar($user, $size = 'xxl') !!}

        <div class="user-name text-truncate">
            {{ $user->getName() }}
        </div>
    </a>
</div>
