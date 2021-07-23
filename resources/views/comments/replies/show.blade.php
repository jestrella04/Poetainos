<div class="reply d-flex justify-content-center">
    <div class="reply-author">
        <a href="{{ $reply->author->path() }}">
            {!! getUserAvatar($reply->author, $size = 'lg') !!}
        </a>
    </div>

    <div class="reply-body flex-grow-1">
        <div class="author">
            <small>
                <i class="fa fa-user" aria-hidden="true"></i>
                {{ __('by') }}
                {{ $reply->author->getName() }}
            </small>

            <small>
                <i class="fa fa-calendar" aria-hidden="true"></i>
                {{ Carbon\Carbon::parse($reply->created_at)->diffForHumans() }}
            </small>
        </div>

        <div class="message">{!! linkify($reply->message) !!}</div>
    </div>
</div>
