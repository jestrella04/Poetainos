@php
    $likers = $writing->likers()->take(5)->inRandomOrder()->get();
@endphp

@if ($writing->likers()->count() > 0)
    <div class="writing-likers">
        <p>
            <small>{{ __('Liked by...') }}</small>
        </p>

        <div class="d-flex flex-wrap">
        @foreach ($writing->likers()->inRandomOrder()->take(5)->get() as $liker)
        <div class="author-link">
            <a href="{{ $liker->path() }}" class="stretched-link" data-toggle="tooltip" data-placement="bottom" title="{{ $liker->getName() }}">
                {!! getUserAvatar($liker) !!}
            </a>
        </div>
        @endforeach
        </div>
    </div>
@endif
