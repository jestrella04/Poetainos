<div class="writing-body">
    <blockquote class="blockquote">
        @if ( $params['single_entry'] ?? false)
            {!! nl2br(e($writing->text)) !!}
        @else
            {!! nl2br(e($writing->excerpt())) !!}...
        @endif
    </blockquote>
</div>
