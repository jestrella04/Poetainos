@foreach ($comment->replies as $reply)
    @include('comments.replies.show')
@endforeach
