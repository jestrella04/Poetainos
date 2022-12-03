@php
    if (isset($params['author'])) {
        $user = $params['author'];
    }
    $count = getUserCounter($user);
    $userList = true;
@endphp

<div class="card">
    <div class="card-body text-center">
        {!! getUserAvatar($user, $size = 'xl') !!}
        <p class="card-title">
            <a href="{{ $user->path() }}" class="stretched-link">{{ $user->getName() }}</a>
        </p>
        <p class="card-subtitle mb-2 text-muted">{{ '@' . $user->username }}</p>

        @if (!empty($user->extra_info['bio']))
            <p class="card-text smaller lead">{{ $user->extra_info['bio'] }}</p>
        @endif
    </div>

    <div class="card-footer">
        @include('users.profile.stats')
    </div>
</div>
