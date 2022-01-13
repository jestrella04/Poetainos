@php
    if (isset($params['author'])) $user = $params['author'];
    $count = getUserCounter($user);
@endphp

<div class="user-banner">
        <div class="user-header d-flex flex-wrap">
            <div>{!! getUserAvatar($user, $size = 'xl') !!}</div>
            <div class="flex-grow-1">
                <a href="{{ $user->path() }}" class="stretched-link">
                    <span class="name">{{ $user->getName() }}</span>
                </a>
                <span class="username text-muted">{{ '@' . $user->username }}</span>
            </div>
        </div>
    </a>

    @if (! empty($user->extra_info['bio']))
    <small class="profile-bio">{{ $user->extra_info['bio'] }}</small>
    @endif
</div>
