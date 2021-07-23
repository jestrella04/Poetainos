<div class="writing-footer">
    @if (count($writing->categories) > 0)
        <div class="footer-item">
            <i class="fas fa-folder-open fa-fw" title="{{ __('Categories') }}" aria-hidden="true"></i>
            @foreach ( $writing->categories as $category )
                <a href="{{ $category->path() }}" class="btn btn-sm btn-secondary d-title">{{ $category->name }}</a>
            @endforeach
        </div>
    @endif

    @if (count($writing->tags) > 0)
        <div class="footer-item">
            <i class="fas fa-hashtag fa-fw" title="{{ __('Tags') }}" aria-hidden="true"></i>
            @foreach ( $writing->tags as $tag )
                <a href="{{ $tag->path() }}" class="btn btn-sm btn-secondary d-title">{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif
</div>
