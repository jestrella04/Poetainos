<div class="comment d-flex justify-content-center">
    <div class="comment-author">
        <a href="{{ $comment->author->path() }}">
            @if (! empty($comment->author->avatarPath()))
                <img class="avatar" src="{{ $comment->author->avatarPath() }}" title="{{ $comment->author->fullName() }}" alt="" loading="lazy">
            @else
                <span class="avatar" title="{{ $comment->author->fullName() }}" >{{ $comment->author->initials() }}</span>
            @endif
        </a>
    </div>

    <div class="comment-body flex-grow-1">
        <div class="author">
            <span>
                <i class="fa fa-calendar"></i>
                {{ Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
            </span>

            <span>
                <i class="fa fa-user"></i>
                {{ __('by') }}
                {{ $comment->author->fullName() }}
            </span>
        </div>

        <div class="message">
            {{ $comment->message }}

            @auth
                <span class="badge badge-dark badge-reply" data-target="#reply-form-{{ $comment->id }}">
                    {{ __('Reply') }}
                </span>
            @endauth
        </div>

        @auth
            <form id="reply-form-{{ $comment->id }}" class="reply-form d-none" action="{{ route('replies.store') }}" method="POST">
                @csrf
                <input type="hidden" name="writing_id" value="{{ $comment->writing->id }}">
                <input type="hidden" name="comment_id" value="{{ $comment->id }}">

                <div class="form-group">
                    <small id="reply-error-{{ $comment->id }}" class="form-text d-none text-danger"></small>
                    <textarea name="reply" class="form-control" maxlength="300" required></textarea>
                </div>

                <button class="btn btn-dark btn-sm btn-block">{{ __('Post Reply') }}</button>
            </form>
        @endauth
    </div>
</div>

<div id="reply-list-{{ $comment->id }}" class="reply-list">
    @if ($comment->replies->count() > 0)
        @include('comments.replies.index')
    @endif
</div>
