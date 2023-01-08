@php
    $sharer = [
        'title' => getPageTitle([$writing->title, $writing->author->getName()]),
        'url' => $writing->path(),
    ];
@endphp

<div class="btn-group dropstart">
    <span role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Actions') }}"
        aria-haspopup="true" role="button">
        <i class="fas fa-ellipsis-v fa-fw" aria-hidden="true"></i>
    </span>

    <div class="dropdown-menu">
        <a href="{{ route('sharer', ['title' => $sharer['title'], 'url' => $sharer['url']]) }}"
            rel="noindex nofollow"
            class="dropdown-item sharer"
            aria-label="{{ __('Share writing :writing', ['writing' => $writing->title]) }}"
            data-wh-title="{{ $sharer['title'] }}" data-wh-url="{{ $sharer['url'] }}">
            {{ __('Share writing') }}
        </a>
        @if (auth()->check() &&
            (auth()->user()->can('update', $writing) ||
                auth()->user()->can('delete', $writing)))
            <a class="dropdown-item"
                href="{{ route('writings.edit', $writing) }}"
                rel="noindex">
                {{ __('Edit / Delete') }}
            </a>
        @endif

        <a class="dropdown-item init-complaint"
            href="{{ route('complaints.create', ['type' => 'writings', 'id' => $writing->id]) }}"
            rel="noindex">
            {{ __('Report writing') }}
        </a>

        @if (auth()->check() &&
            !auth()->user()->is($writing->author))
            <a class="dropdown-item init-block-user"
                href="{{ route('users.block.confirm', ['user' => $writing->author->username]) }}"
                rel="noindex">
                {{ __('Block writer') }}
            </a>
        @endif
    </div>
</div>
