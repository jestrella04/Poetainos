@php
    $count = getCommentLikeCounter($comment);
    $liked = isCommentLiked($comment);
@endphp

<div id="comment-{{ $comment->id }}" class="d-flex comment">
    <div class="flex-shrink-0">
        <a href="{{ $comment->author->path() }}">
            {!! getUserAvatar($comment->author, $size = 'lg') !!}
        </a>
    </div>

    <div class="flex-grow-1 comment-body">
        <div class="d-flex justify-content-between">
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

            <div class="menu">
                @include('comments.dropdown')
            </div>
        </div>

        <div class="message smaller">
            {!! linkify($comment->message) !!}
        </div>

        <div class="buttons d-flex justify-content-between stats">
            <span @class(['btn', 'btn-light', 'btn-xs', 'likeable', 'liked' => $liked > 0])
                data-wh-target-guest="{{ route('socialite') }}"
                data-wh-target-store="{{ route('likes.store', ['type' => 'comment', 'id' => $comment->id]) }}"
                data-wh-target-delete="{{ route('likes.destroy', ['type' => 'comment', 'id' => $comment->id]) }}">
                <i class="fas fa-heart" aria-hidden="true"></i>
                <span class="counter">{{ $count['readable'] }}</span>
            </span>

            @auth
                <span class="btn btn-light btn-xs badge-reply" data-wh-target="#reply-form-{{ $comment->id }}">
                    <i class="fas fa-reply" aria-hidden="true"></i>
                    {{ __('Reply') }}
                </span>
            @endauth
        </div>

        @auth
            <form id="reply-form-{{ $comment->id }}" class="comment-form d-none" action="{{ route('comments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="reply_to" value="{{ $comment->author->username }}">
                <input type="hidden" name="writing_id" value="{{ $comment->writing->id }}">

                <small id="reply-error-{{ $comment->id }}" class="form-text d-none text-danger"></small>
                <textarea
                    name="comment"
                    class="form-control autogrow commentbox smaller"
                    placeholder="{{ __('Use the @ symbol to tag other users') }}"
                    aria-label="{{ __('Use the @ symbol to tag other users') }}"
                    maxlength="300"
                    required></textarea>

                <div class="d-grid">
                    <button class="btn btn-primary btn-sm">{{ __('Post Reply') }}</button>
                </div>
            </form>
        @endauth
    </div>
</div>
