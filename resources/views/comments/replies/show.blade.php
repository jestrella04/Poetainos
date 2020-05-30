<div class="reply d-flex justify-content-center">
    <div class="reply-author">
        <a href="{{ $reply->author->path() }}">
            @if (! empty($reply->author->avatarPath()))
                <img class="avatar" src="{{ $reply->author->avatarPath() }}" title="{{ $reply->author->fullName() }}" alt="" loading="lazy">
            @else
                <span class="avatar" title="{{ $reply->author->fullName() }}" >{{ $reply->author->initials() }}</span>
            @endif
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
                {{ $reply->author->fullName() }}
            </span>
        </div>

        <div class="message">{{ $reply->message }}</div>
    </div>
</div>
