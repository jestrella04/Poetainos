<div class="comment-list">
    @foreach ($comments->reverse() as $comment)
        @include('comments.show')
    @endforeach
</div>
