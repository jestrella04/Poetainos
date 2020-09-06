<div class="writing-entry">
    @include('writings.entry.header')
    @include('writings.entry.body')

    @if ($params['single_entry'] ?? false)
        @if (! empty($writing->externalLink()))
            <div class="writing-link">
                <a href="{{ $writing->externalLink() }}" class="btn btn-link" target="_blank" rel="nofollow">
                    <i class="fa fa-link fa-fw"></i>
                    {{ $writing->externalLink() }}
                </a>
            </div>
        @endif

        @include('writings.entry.footer')
        @include('writings.entry.stats')
        @include('writings.entry.likers')
    @else
        <div class="d-flex flex-column flex-md-row">
            <div class="btn-read-more">
                <a href="{{ $writing->path() }}" class="btn btn-primary btn-sm">{{ __('Read more') }}</a>
            </div>

            @include('writings.entry.stats')
        </div>
    @endif
</div>

@if (isset($loop) && ! $loop->last)
    <hr>
@endif

@if ($params['single_entry'] ?? false)
    <hr>

    @include('writings.entry.comments')
@endif
