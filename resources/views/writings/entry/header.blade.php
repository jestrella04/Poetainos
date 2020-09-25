<div class="writing-heading">
    <div class="d-flex flex-column flex-md-row justify-content-center">
        <div class="writing-author align-self-center">
            <a href="{{ $writing->author->path() }}">
                {!! getUserAvatar($writing->author) !!}
            </a>
        </div>

        <div class="flex-fill">
            <div class="d-flex flex-column">
                <div class="writing-meta">
                    <small>
                        <i class="fas fa-user fa-fw"></i>
                        {{ __('by') }}
                        {{ $writing->author->getName() }}
                    </small>

                    <small>
                        @if ('home' === Route::current()->getName())
                            <i class="fas fa-award fa-fw"></i>
                            {{ Carbon\Carbon::parse($writing->home_posted_at)->diffForHumans() }}
                        @else
                            <i class="fas fa-calendar fa-fw"></i>
                            {{ Carbon\Carbon::parse($writing->created_at)->diffForHumans() }}
                            @endif
                    </small>
                </div>

                <h2 class="writing-title">
                    @if ( $params['single_entry'] ?? false )
                        {{ $writing->title }}
                    @else
                        <a href="{{ $writing->path() }}">{{ $writing->title }}</a>
                    @endif
                </h2>
            </div>
        </div>
    </div>
</div>

@if (! empty($writing->coverPath()))
    <div class="writing-cover">
        <img src="{{ $writing->coverPath() }}" alt="" loading="lazy">
    </div>
@endif
