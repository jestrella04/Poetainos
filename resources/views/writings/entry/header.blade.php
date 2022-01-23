<div class="writing-heading">
    <div class="d-flex flex-column flex-md-row justify-content-center">
        <div class="writing-author align-self-center">
            <a href="{{ $writing->author->path() }}">
                {!! getUserAvatar($writing->author, $size = 'xl') !!}
            </a>
        </div>

        <div class="flex-fill">
            <div class="d-flex flex-column">
                <div class="writing-meta">
                    <span>
                        <i class="fas fa-user fa-fw" aria-hidden="true"></i>
                        {{ __('by') }}
                        {{ $writing->author->getName() }}
                    </span>

                    <span>
                        <i class="fas fa-calendar fa-fw" aria-hidden="true"></i>
                        {{ Carbon\Carbon::parse($writing->created_at)->diffForHumans() }}
                    </span>
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
        <img src="{{ $writing->coverPath() }}" width="1280" height="720" alt="" loading="lazy">
    </div>
@endif
