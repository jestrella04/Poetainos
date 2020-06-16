@php
    if (isset($params['author'])) $user = $params['author']
@endphp

<div class="user-banner text-center">
    <a href="{{ $user->path() }}" class="avatar-link" title="{{ __('View profile') }}" data-toggle="tooltip">
        {!! getUserAvatar($user) !!}

        <div class="user-name text-truncate">
            {{ $user->getName() }}
        </div>
    </a>
</div>
