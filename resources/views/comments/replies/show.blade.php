<div class="reply d-flex justify-content-center">
    <div class="reply-author">
        <a href="{{ $reply->author->path() }}" title="{{ __('View profile') }}" data-toggle="tooltip">
            {!! getUserAvatar($reply->author) !!}
        </a>
    </div>

    <div class="reply-body flex-grow-1">
        <div class="author">
            <span>
                <i class="fa fa-calendar"></i>
                {{ Carbon\Carbon::parse($reply->created_at)->diffForHumans() }}
            </span>

            <span>
                <i class="fa fa-user"></i>
                {{ __('by') }}
                {{ $reply->author->getName() }}
            </span>
        </div>

        <div class="message">{{ $reply->message }}</div>
    </div>
</div>
