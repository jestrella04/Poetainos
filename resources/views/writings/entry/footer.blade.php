<div class="writing-footer">
    @if (! empty($writing->category))
        <p>
            <i class="fas fa-folder fa-fw" title="{{ __('Category') }}"></i>
            <a href="{{ $writing->category->path() }}" class="btn btn-outline-dark btn-sm">{{ $writing->category->name }}</a>
        </p>
    @endif

    @if (count($writing->tags) > 0)
        <p>
            <i class="fas fa-hashtag fa-fw" title="{{ __('Tags') }}"></i>
            @foreach ( $writing->tags as $tag )
                <a href="{{ $tag->path() }}" class="btn btn-outline-dark btn-sm">{{ $tag->name }}</a>
            @endforeach
        </p>
    @endif
</div>
