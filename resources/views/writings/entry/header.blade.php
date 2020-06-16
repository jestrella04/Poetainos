<div class="writing-heading">
    <div class="d-flex flex-column flex-md-row justify-content-center">
        <div class="writing-author align-self-center">
            <a href="{{ $writing->author->path() }}"
                title="{{ __('View profile') }}"
                data-toggle="tooltip"
                data-placement="top">
                @if (! empty($writing->author->avatarPath()))
                    <img class="avatar" src="{{ $writing->author->avatarPath() }}" alt="" loading="lazy">
                @else
                    <span class="avatar">{{ $writing->author->initials() }}</span>
                @endif
            </a>
        </div>

        <div class="flex-fill">
            <div class="d-flex flex-column">
                <div class="writing-meta">
                    <small class="mr-2">
                        <i class="fas fa-calendar fa-fw"></i>
                        {{ Carbon\Carbon::parse($writing->created_at)->diffForHumans() }}
                    </small>

                    <small>
                        <i class="fas fa-user fa-fw"></i>
                        {{ __('by') }}
                        {{ $writing->author->getName() }}
                    </small>
                </div>

                <h3 class="writing-title">
                    @if ( $params['single_entry'] ?? false )
                        {{ $writing->title }}
                    @else
                        <a href="{{ $writing->path() }}">{{ $writing->title }}</a>
                    @endif
                </h3>
            </div>
        </div>
    </div>
</div>

@if (! empty($writing->coverPath()))
    <div class="writing-cover">
        <img src="{{ $writing->coverPath() }}" alt="" loading="lazy">
    </div>
@endif
