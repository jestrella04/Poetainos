<div class="writing-comments">
    <div id="post-comment" class="main-content">
        @auth
            <form id="post-comment-form" action="{{ route('comments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="writing_id" value="{{ $writing->id }}">

                <div class="form-group">
                    <small id="post-comment-success" class="form-text d-none text-success">{{ __('Comment posted successfully') }}</small>
                    <small id="post-comment-error" class="form-text d-none text-danger"></small>
                    <textarea name="comment" class="form-control" placeholder="{{ __('Leave your comment here') }}" maxlength="300" required></textarea>
                </div>

                <button class="btn btn-dark btn-sm btn-block">{{ __('Post Comment') }}</button>
            </form>
        @else
            <div class="text-center">
                <p>{{ __('Please login to your account before you can comment.') }}</p>
                <a href="{{ route('login') }}" class="btn btn-dark btn-sm">{{ __('Login') }}</a>
            </div>
        @endauth
    </div>

    <hr>

    @if ($writing->comments()->count() > 0)
        @section('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    loadComments('{{ route('comments.index', $writing->id) }}');
                });
            </script>
        @endsection

        <h3 class="all-caps">{{ __('Comments') }}</h3>

        <div id="load-more" class="d-none">
            <button class="btn btn-link btn-sm btn-block" data-href="">{{ __('Load older comments') }}</button>
        </div>

        <div id="loading-comments" class="text-center">
            <i class="fa fa-spinner fa-5x fa-spin"></i>
            <p>{{ __('Loading comments') }}</p>
        </div>
    @else
        <div class="comments-empty">
            <p>{{ __('Do you like this writing?') }}</p>
            <p class="text-muted">{{ __('Be the first to leave a comment.') }}</p>
            <i class="fas fa-comment fa-5x"></i>
        </div>
    @endif

    <div id="embed-comments">
        <div class="comment-list"></div>
    </div>
</div>
