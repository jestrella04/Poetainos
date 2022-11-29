<div id="comment-{{ $comment->id }}" class="d-flex comment">
    <div class="flex-shrink-0">
        <a href="{{ $comment->author->path() }}">
            {!! getUserAvatar($comment->author, $size = 'lg') !!}
        </a>
    </div>

    <div class="flex-grow-1 comment-body">
        <div class="meta">
            <span>
                <i class="fa fa-user" aria-hidden="true"></i>
                {{ $comment->author->getName() }}
            </span>

            <span>
                <i class="fa fa-calendar" aria-hidden="true"></i>
                {{ Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
            </span>
        </div>

        <div class="message">
            {!! linkify($comment->message) !!}
        </div>

        <div class="buttons d-flex justify-content-end">
            {{-- <button class="btn btn-sm btn-link">Like</button> --}}
            @auth
                <button type="button" class="btn btn-light badge-reply" data-wh-target="#reply-form-{{ $comment->id }}">
                    <i class="fas fa-reply" aria-hidden="true"></i>
                    {{ __('Reply') }}
                </button>
            @endauth
        </div>

        @auth
            <form id="reply-form-{{ $comment->id }}" class="reply-form d-none" action="{{ route('replies.store') }}" method="POST">
                @csrf
                <input type="hidden" name="writing_id" value="{{ $comment->writing->id }}">
                <input type="hidden" name="comment_id" value="{{ $comment->id }}">
                <small id="reply-error-{{ $comment->id }}" class="form-text d-none text-danger"></small>
                <textarea
                    name="reply"
                    class="form-control autogrow commentbox"
                    placeholder="{{ __('Leave your reply here. You can mention other users by using @') }}"
                    aria-label="{{ __('Leave your reply here. You can mention other users by using @') }}"
                    maxlength="300"
                    required></textarea>

                <div class="d-grid">
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
