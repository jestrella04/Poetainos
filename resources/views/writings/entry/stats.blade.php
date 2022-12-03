@php
    $count = getWritingCounter($writing);

    if (auth()->check()) {
        $userId = auth()->user()->id;
        $voted = App\Models\Vote::where('user_id', $userId)
            ->where('writing_id', $writing->id)
            ->value('vote');
        $shelved = App\Models\Shelf::where('user_id', $userId)
            ->where('writing_id', $writing->id)
            ->value('writing_id');
    }
@endphp

<div class="d-flex stats writing-stats">
    @if (isset($writing->home_posted_at))
        <span class="badge flex-fill" title="{{ __('Awarded a Golden Flower') }}">
            <i class="fas fa-fan" style="color:goldenrod" aria-hidden="true"></i>
            <span class="visually-hidden">{{ __('Awarded a Golden Flower') }}</span>
            <span>:</span>
        </span>
    @endif

    <span class="badge flex-fill click like @if (isset($voted) && $voted > 0) {{ 'voted' }} @endif"
        title="{{ __(':count Likes', ['count' => $count['likes']['counter']]) }}"
        @if (auth()->check() && empty($vote)) data-wh-target="{{ route('votes.store') }}"
        data-wh-id="{{ $writing->id }}"
        data-wh-value="1" @endif>
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

    <span
        class="badge flex-fill @if (isset($userId) && $userId !== $writing->author->id) {{ 'click shelf' }} @endif @if (isset($shelved) && $shelved === $writing->id) {{ 'shelved' }} @endif"
        title="{{ __(':count Shelved', ['count' => $count['shelf']['counter']]) }}"
        @if (auth()->check()) data-wh-target="{{ route('shelves.store') }}"
        data-wh-id="{{ $writing->id }}" @endif>
        <i class="fa fa-bookmark" aria-hidden="true"></i>
        <span class="counter">{{ $count['shelf']['readable'] }}</span>
    </span>

    <span class="badge flex-fill" title="{{ __('Aura: :aura', ['aura' => $count['aura']['counter']]) }}">
        <i class="fa fa-dove" aria-hidden="true"></i>
        <span class="counter">{{ $count['aura']['readable'] }}</span>
    </span>
</div>
