<div class="card-body">
    <h2 class="card-title text-center">
        @if ( $params['single_entry'] ?? false )
            {{ $writing->title }}
        @else
            <a href="{{ $writing->path() }}">{{ $writing->title }}</a>
        @endif
    </h2>

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
        <blockquote class="position-relative">
            @if ($params['single_entry'] ?? false)
                {!! nl2br(e($writing->text)) !!}
            @else
                {!! nl2br(e($writing->excerpt())) !!}

                <a href="{{ $writing->path() }}"
                    @if (mb_strlen($writing->text) > 400)
                    title="{{ __(':chars more characters.', ['chars' => mb_strlen($writing->text) - 400]) }}"
                    @endif
                    class="stretched-link">
                    {{-- {{ __('Continue reading') }} --}}
                </a>
            @endif
        </blockquote>
    </div>

    @if ($params['single_entry'] ?? false)
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
    @else
        @include('writings.entry.stats')
    @endif
</div>
