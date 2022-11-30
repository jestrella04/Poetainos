<div class="btn-group dropstart">
    <span role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Actions') }}"
        aria-haspopup="true" role="button">
        <i class="fas fa-ellipsis-v fa-fw" aria-hidden="true"></i>
    </span>

    <div class="dropdown-menu">
        @can('delete', $comment)
            <a href="{{ route('comments.destroy', $comment) }}" class="dropdown-item disabled">
                {{ __('Delete comment') }}
            </a>
        @endcan

        <a class="dropdown-item init-complaint"
            href="{{ route('complaints.create', ['type' => 'comments', 'id' => $comment->id]) }}">
            {{ __('Report comment') }}
        </a>

        @if (auth()->check() &&
            !auth()->user()->is($comment->author))
            <a href="{{ route('users.block.confirm', ['user' => $comment->author->username]) }}"
                class="dropdown-item init-block-user">
                {{ __('Block user') }}
            </a>
        @endif
    </div>
</div>
