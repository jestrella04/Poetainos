<div id="comment-{{ $comment->id }}" class="comment d-flex justify-content-center">
    <div class="comment-author">
        <a href="{{ $comment->author->path() }}">
            {!! getUserAvatar($comment->author, $size = 'xl') !!}
        </a>
    </div>

    <div class="comment-body flex-grow-1">
        <div class="author">
            <small>
                <i class="fa fa-user" aria-hidden="true"></i>
                {{ __('by') }}
                {{ $comment->author->getName() }}
            </small>

            <small>
                <i class="fa fa-calendar" aria-hidden="true"></i>
                {{ Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
            </small>
        </div>

        <div class="message">
            {!! linkify($comment->message) !!}

            @auth
                <span class="badge bg-primary badge-reply" data-wh-target="#reply-form-{{ $comment->id }}">
                    {{ __('Reply') }}
                </span>
            @endauth
        </div>

        @auth
            <form id="reply-form-{{ $comment->id }}" class="reply-form d-none" action="{{ route('replies.store') }}" method="POST">
                @csrf
                <input type="hidden" name="writing_id" value="{{ $comment->writing->id }}">
                <input type="hidden" name="comment_id" value="{{ $comment->id }}">

                <div class="mb-3">
                    <small id="reply-error-{{ $comment->id }}" class="form-text d-none text-danger"></small>

                    <textarea
                        name="reply"
                        class="form-control autogrow commentbox"
                        placeholder="{{ __('Leave your reply here. You can mention other users by using @') }}"
                        aria-label="{{ __('Leave your reply here. You can mention other users by using @') }}"
                        maxlength="300"
                        required></textarea>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-primary">{{ __('Post Reply') }}</button>
                </div>
            </form>
        @endauth
    </div>
</div>

<div id="reply-list-{{ $comment->id }}" class="reply-list">
    @if ($comment->replies->count() > 0)
        @include('comments.replies.index')
    @endif
</div>
