@php
    $count = getWritingCounter($writing);
    $liked = isWritingLiked($writing);
    $shelved = isWritingShelved($writing);
    $userId = auth()->check() ? auth()->user()->id : null;
@endphp

<div class="d-flex stats writing-stats">
    @if (isset($writing->home_posted_at))
        <span class="badge flex-fill" title="{{ __('Awarded a Golden Flower') }}">
            <i class="fas fa-fan" style="color:goldenrod" aria-hidden="true"></i>
            <span class="visually-hidden">{{ __('Awarded a Golden Flower') }}</span>
            <span>:</span>
        </span>
    @endif

    <span @class(['badge', 'flex-fill', 'click', 'likeable', 'liked' => $liked > 0])
        title="{{ __(':count Likes', ['count' => $count['likes']['counter']]) }}"
        data-wh-target-guest="{{ route('socialite') }}"
        data-wh-target-store="{{ route('likes.store', ['type' => 'writing', 'id' => $writing->id]) }}"
        data-wh-target-delete="{{ route('likes.destroy', ['type' => 'writing', 'id' => $writing->id]) }}">
        <i class="fa fa-heart" aria-hidden="true"></i>
        <span class="counter">{{ $count['likes']['readable'] }}</span>
    </span>

    <span class="badge flex-fill" title="{{ __(':count Comments', ['count' => $count['comments']['counter']]) }}">
        <i class="fa fa-comment" aria-hidden="true"></i>
        <span class="counter">{{ $count['comments']['readable'] }}</span>
    </span>

    <span class="badge flex-fill" title="{{ __(':count Views', ['count' => $count['views']['counter']]) }}">
        <i class="fa fa-book-reader" aria-hidden="true"></i>
        <span class="counter">{{ $count['views']['readable'] }}</span>
    </span>

    <span @class([
            'badge',
            'flex-fill',
            'click shelf' => auth()->guest() || (isset($userId) && $userId !== $writing->author->id),
            'shelved' => $shelved > 0])
        data-wh-target-guest="{{ route('socialite') }}"
        data-wh-target-store="{{ route('shelves.store', $writing) }}"
        data-wh-target-delete="{{ route('shelves.destroy', $writing) }}"
        title="{{ __(':count Shelved', ['count' => $count['shelf']['counter']]) }}">
        <i class="fa fa-bookmark" aria-hidden="true"></i>
        <span class="counter">{{ $count['shelf']['readable'] }}</span>
    </span>

    <span class="badge flex-fill" title="{{ __('Aura: :aura', ['aura' => $count['aura']['counter']]) }}">
        <i class="fa fa-dove" aria-hidden="true"></i>
        <span class="counter">{{ $count['aura']['readable'] }}</span>
    </span>
</div>
