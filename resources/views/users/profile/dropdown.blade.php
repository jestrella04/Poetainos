@php
    $sharer = [
        'title' => $params['title'],
        'url' => $user->path(),
    ];
@endphp

<div class="btn-group dropstart">
    <span role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Actions') }}"
        aria-haspopup="true" role="button">
        <i class="fas fa-ellipsis-v fa-fw" aria-hidden="true"></i>
    </span>

    <div class="dropdown-menu">
        <a href="{{ route('sharer', ['title' => $sharer['title'], 'url' => $sharer['url']]) }}"
            class="dropdown-item sharer" data-wh-title="{{ $sharer['title'] }}" data-wh-url="{{ $sharer['url'] }}">
            {{ __('Share profile') }}
        </a>
        @can('update', $user)
            <a href="{{ route('users.edit', $user) }}" class="dropdown-item">
                {{ __('Update profile') }}
            </a>
        @endcan
        @if ($user->writings()->count() > 0)
            <a href="{{ $user->writingsPath() }}" class="dropdown-item">
                @if (auth()->check() &&
                    auth()->user()->is($user))
                    {{ __('View my writings') }}
                @else
                    {{ __('View writings') }}
                @endif
            </a>
        @endif
        @if ($user->Shelf()->count() > 0)
            <a href="{{ $user->shelfPath() }}" class="dropdown-item">
                @if (auth()->check() &&
                    auth()->user()->is($user))
                    {{ __('View my shelf') }}
                @else
                    {{ __('View shelf') }}
                @endif
            </a>
        @endif
        @if (auth()->check() &&
            !auth()->user()->is($user))
            <a href="{{ route('users.block.confirm', ['user' => $user->username]) }}"
                class="dropdown-item init-block-user">
                {{ __('Block user') }}
            </a>
        @endif
    </div>
</div>
