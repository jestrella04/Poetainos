<div class="writing-footer">
    @if (! empty($writing->categories))
        <div class="footer-item">
            <i class="fas fa-folder-open fa-fw" title="{{ __('Categories') }}"></i>
            @foreach ( $writing->categories as $category )
                <a href="{{ $category->path() }}" class="badge badge-light d-title">{{ $category->name }}</a>
            @endforeach
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
