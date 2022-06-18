@php
    $count = getWritingCounter($writing);

    if (auth()->check()) {
        $userId = auth()->user()->id;
        $voted = App\Vote::where('user_id', $userId)->where('writing_id', $writing->id)->value('vote');
        $shelved = App\Shelf::where('user_id', $userId)->where('writing_id', $writing->id)->value('writing_id');
    }
@endphp

<div class="d-flex flex-wrap justify-content-evenly stats writing-stats">
    @if (isset($writing->home_posted_at))
        <span
            class="badge"
            title="{{ __('Awarded a Golden Flower') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="top">
            <i class="fas fa-fan fa-fw" style="color:goldenrod" aria-hidden="true"></i>
            <span class="visually-hidden">{{ __('Awarded a Golden Flower') }}</span>
        </span>
    @endif

    <span
        class="badge click like @if (isset($voted) && $voted > 0) {{ 'voted' }} @endif"
        title="{{ __(':count Likes', ['count' => $count['likes']['counter']]) }}"
        @if (auth()->check() && empty($vote))
        data-wh-target="{{ route('votes.store') }}"
        data-wh-id="{{ $writing->id }}"
        data-wh-value="1"
        @endif
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-heart fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['likes']['readable'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __(':count Comments', ['count' => $count['comments']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-comment fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['comments']['readable'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __(':count Views', ['count' => $count['views']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-book-reader fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['views']['readable'] }}</span>
    </span>

    <span
        class="badge @if (isset($userId) && $userId !== $writing->author->id) {{ 'click shelf' }} @endif @if (isset($shelved) && $shelved === $writing->id) {{ 'shelved' }} @endif"
        title="{{ __(':count Shelved', ['count' => $count['shelf']['counter']]) }}"
        @if (auth()->check())
        data-wh-target="{{ route('shelves.store') }}"
        data-wh-id="{{ $writing->id }}"
        @endif
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-bookmark fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['shelf']['readable'] }}</span>
    </span>

    <span
        class="badge"
        title="{{ __('Aura: :aura', ['aura' => $count['aura']['counter']]) }}"
        data-bs-toggle="tooltip"
        data-bs-placement="top">
        <i class="fa fa-dove fa-fw" aria-hidden="true"></i>
        <span class="counter">{{ $count['aura']['readable'] }}</span>
    </span>

    <div class="dropdown dropdown-counter d-inline">
        <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Share writing') }}">
            <span
                class="badge click share"
                id="dropdown-share-{{ $writing->id }}"
                data-wh-url="{{ $writing->path() }}"
                data-wh-writing-title="{{ $writing->title }}"
                data-bs-toggle="dropdown"
                aria-label="{{ __('Share writing :writing', ['writing' => $writing->title]) }}"
                aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-share-alt fa-fw" aria-hidden="true"></i>
            </span>

            <div class="dropdown-menu" aria-labelledby="dropdown-share-{{ $writing->id }}">
                @foreach ($writing->shareLinks() as $serviceName => $serviceData)
                    <a class="dropdown-item {{ $serviceData['class'] }}"
                        href="{{ $serviceData['url'] }}"
                        rel="noopener">
                        <i class="{{ $serviceData['icon']}}" aria-hidden="true"></i>
                        {{ ucfirst($serviceName) }}
                    </a>
                @endforeach
            </div>
        </span>

    </div>

    <div class="dropdown dropdown-counter d-inline">
        <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Actions') }}">
            <span
                class="badge click owner"
                id="dropdown-owner"
                data-bs-toggle="dropdown"
                aria-label="{{ __('Actions') }}"
                aria-haspopup="true"
                aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-fw" aria-hidden="true"></i>
            </span>

            <div class="dropdown-menu" aria-labelledby="dropdown-owner">
                @if (auth()->check() && (auth()->user()->can('update', $writing) || auth()->user()->can('delete', $writing)))
                    <a class="dropdown-item" href="{{ route('writings.edit', $writing) }}">
                        {{ __('Edit / Delete') }}
                    </a>
                @endif

                <a class="dropdown-item init-complaint"
                    href="{{ route('complaints.create', ['type' => 'writings', 'id' => $writing->id]) }}">
                    {{ __('Report writing') }}
                </a>

                @if (auth()->check() && ! auth()->user()->is($writing->author))
                    <a class="dropdown-item init-block-user"
                        href="{{ route('users.block.confirm', ['user' => $writing->author->username]) }}">
                        {{ __('Block writer') }}
                    </a>
                @endif
            </div>
        </span>
    </div>
</div>
