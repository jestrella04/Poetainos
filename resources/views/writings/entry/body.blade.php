<div class="card-body">
    <p class="h4 card-title text-center">
        @if ( $params['writings_single_entry'] ?? false )
            {{ $writing->title }}
        @else
            <a href="{{ $writing->path() }}">{{ $writing->title }}</a>
        @endif
    </p>

    <p class="card-subtitle text-center">
        <span>
            <i class="fas fa-user fa-fw" aria-hidden="true"></i>
            {{ __('by') }}
            {{ $writing->author->getName() }}
        </span>

        <span>
            <i class="fas fa-calendar fa-fw" aria-hidden="true"></i>
            {{ Carbon\Carbon::parse($writing->created_at)->diffForHumans() }}
        </span>
    </p>

    <div class="card-text writing-body">
        <blockquote>
            @if ($params['writings_single_entry'] ?? false)
                {!! nl2br(e($writing->text)) !!}
            @else
            <div class="writing-read-more" data-link="{{ $writing->path() }}">
                {!! nl2br(e($writing->excerpt())) !!}
            </div>
            @endif
        </blockquote>
    </div>

    @if ($params['writings_single_entry'] ?? false)
        @if (! empty($writing->externalLink()))
            <div class="writing-link">
                <i class="fa fa-link fa-fw" aria-hidden="true"></i>
                <a href="{{ $writing->externalLink() }}" class="btn btn-link" target="_blank" rel="noopener">
                    {{ cropify($writing->externalLink()) }}
                </a>
            </div>
        @endif

        @include('writings.entry.footer')
        @include('writings.entry.stats')
        @include('writings.entry.likers')
    @endif
</div>

@if (! ($params['writings_single_entry'] ?? false))
    <div class="card-footer">
        @include('writings.entry.stats')
    </div>
@endif
