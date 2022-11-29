<div id="comment-reply-{{ $reply->id }}" class="d-flex reply">
    <div class="flex-shrink-0">
        <a href="{{ $reply->author->path() }}">
            {!! getUserAvatar($reply->author, $size = 'md') !!}
        </a>
    </div>

    <div class="flex-grow-1 reply-body">
        <div class="meta">
            <span>
                <i class="fa fa-user" aria-hidden="true"></i>
                {{ $reply->author->getName() }}
            </span>

            <span>
                <i class="fa fa-calendar" aria-hidden="true"></i>
                {{ Carbon\Carbon::parse($reply->created_at)->diffForHumans() }}
            </span>
        </div>

        <div class="message">
            {!! linkify($reply->message) !!}
        </div>
    </div>
</div>
