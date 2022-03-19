<div class="writing-body">
    <blockquote>
        @if ( $params['single_entry'] ?? false)
            {!! nl2br(e($writing->text)) !!}
        @else
            {!! nl2br(e($writing->excerpt())) !!}
        @endif
    </blockquote>
</div>
