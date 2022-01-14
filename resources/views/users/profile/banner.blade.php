@php
    if (isset($params['author'])) $user = $params['author'];
    $count = getUserCounter($user);
@endphp

<div class="user-banner">
    <div class="user-header d-flex flex-wrap">
        <div>{!! getUserAvatar($user, $size = 'xl') !!}</div>
        <div class="flex-grow-1">
            <a href="{{ $user->path() }}" class="stretched-link">
                <span class="d-block">{{ $user->getName() }}</span>
            </a>
            <span class="d-block text-muted">{{ '@' . $user->username }}</span>
        </div>
    </div>

    @if (! empty($user->extra_info['bio']))
    <p class="profile-bio smaller">{{ $user->extra_info['bio'] }}</p>
    @endif

    @include('users.profile.stats')
</div>
