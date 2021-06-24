@php
    $likers = $writing->likers()->take(5)->inRandomOrder()->get();
@endphp

@if ($writing->likers()->count() > 0)
    <div class="writing-likers">
        <small>{{ __('Liked by...') }}</small>

        <div class="d-flex flex-wrap">
            @foreach ($writing->likers()->inRandomOrder()->take(5)->get() as $liker)
            <div class="author-link">
                <a href="{{ $liker->path() }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ $liker->getName() }}">
                    {!! getUserAvatar($liker, $size = 'xl') !!}
                </a>
            </div>
            @endforeach
        </div>
    </div>
@endif
