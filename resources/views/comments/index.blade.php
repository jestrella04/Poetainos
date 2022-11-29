<div class="comment-tl"></div>

@foreach ($comments->reverse() as $comment)
    @include('comments.show')
@endforeach
