<div class="writing-footer">
    @if (count($writing->categories) > 0)
        <div class="d-flex footer-item">
            <div>
                <i class="fas fa-folder-open fa-fw" title="{{ __('Categories') }}" aria-hidden="true"></i>
            </div>

            <div>
                @foreach ( $writing->categories as $category )
                    <a href="{{ $category->path() }}" class="badge bg-primary d-title">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>
    @endif

    @if (count($writing->tags) > 0)
        <div class="d-flex footer-item">
            <div>
                <i class="fas fa-hashtag fa-fw" title="{{ __('Tags') }}" aria-hidden="true"></i>
            </div>

            <div>
                @foreach ( $writing->tags as $tag )
                    <a href="{{ $tag->path() }}" class="badge bg-success d-title">{{ $tag->name }}</a>
                @endforeach
            </div>
        </div>
    @endif
</div>
