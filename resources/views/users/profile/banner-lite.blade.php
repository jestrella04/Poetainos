@php
    if (isset($params['author'])) $user = $params['author']
@endphp

<div class="user-banner-lite text-center">
    {!! getUserAvatar($user, $size = 'xxl') !!}
    <a href="{{ $user->path() }}" class="stretched-link">
        <span class="name">{{ $user->getName() }}</span>
    </a>
    <span class="username text-muted">{{ '@' . $user->username }}</span>
</div>
