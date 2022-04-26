<div class="writing-entry">
    @include('writings.entry.header')
    @include('writings.entry.body')

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

@if (isset($loop) && ! $loop->last)
    <hr>
@endif

@if ($params['single_entry'] ?? false)
    <hr>

    @include('writings.entry.comments')
@endif
