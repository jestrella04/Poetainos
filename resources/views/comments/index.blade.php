@foreach ($comments->reverse() as $comment)
    @include('comments.show')
@endforeach
