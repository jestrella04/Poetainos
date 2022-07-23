<div class="cover">
    @if (! empty($writing->coverPath()))
        <div class="writing-cover">
            <img src="{{ $writing->coverPath() }}" class="card-img-top" width="1280" height="720" alt="" loading="lazy">
        </div>
    @endif

    <div class="writing-author align-self-center">
        <a href="{{ $writing->author->path() }}">
            {!! getUserAvatar($writing->author, $size = 'xxl') !!}
        </a>
    </div>
</div>
