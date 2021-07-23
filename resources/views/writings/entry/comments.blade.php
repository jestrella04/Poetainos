<div class="writing-comments">
    <div id="post-comment" class="main-content">
        @auth
            <form id="post-comment-form" action="{{ route('comments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="writing_id" value="{{ $writing->id }}">

                <div class="mb-3">
                    <small id="post-comment-success" class="form-text d-none text-success">{{ __('Comment posted successfully') }}</small>
                    <small id="post-comment-error" class="form-text d-none text-danger"></small>

                    <textarea
                        name="comment"
                        class="form-control autogrow"
                        placeholder="{{ __('Leave your comment here') }}"
                        aria-label="{{ __('Leave your comment here') }}"
                        maxlength="300"
                        required></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary">{{ __('Post Comment') }}</button>
                </div>
            </form>
        @else
            <div class="text-center">
                <p>{{ __('Please login to your account before you can comment.') }}</p>

                <div class="socialite">
                    @include('partials.socialite')

                    <a href="{{ route('login') }}"
                        class="btn btn-success"
                        title="{{ __('Login with email') }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top">
                        <i class="fas fa-fw fa-at" aria-hidden="true"></i>
                    </a>
                </div>
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
            <div class="d-grid gap-2">
                <button class="btn btn-link btn-sm" data-wh-href="">{{ __('Load older comments') }}</button>
            </div>
        </div>

        <div id="loading-comments" class="text-center">
            <i class="fa fa-spinner fa-5x fa-spin" aria-hidden="true"></i>
            <p>{{ __('Loading comments') }}</p>
        </div>
    @else
        <div class="comments-empty">
            <p>{{ __('Do you like this writing?') }}</p>
            <p class="text-muted">{{ __('Be the first to leave a comment.') }}</p>
            <i class="fas fa-comment fa-5x" aria-hidden="true"></i>
        </div>
    @endif

    <div id="embed-comments">
        <div class="comment-list"></div>
    </div>
</div>
