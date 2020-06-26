<div class="writing-footer">
    @if (! empty($writing->category))
        <div class="footer-item">
            <i class="fas fa-folder-open fa-fw" title="{{ __('Category') }}"></i>
            <a href="{{ $writing->category->path() }}" class="badge badge-light d-title">{{ $writing->category->name }}</a>
        </div>
    @endif

    @if (count($writing->tags) > 0)
        <div class="footer-item">
            <i class="fas fa-hashtag fa-fw" title="{{ __('Tags') }}"></i>
            @foreach ( $writing->tags as $tag )
                <a href="{{ $tag->path() }}" class="badge badge-light d-title">{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif
</div>
