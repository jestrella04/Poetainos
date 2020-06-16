<div class="writing-body">
    <p>
        @if ( $params['single_entry'] ?? false)
            {{!! nl2br(e($writing->text)) !!}}
        @else
            {{!! nl2br(e($writing->excerpt())) !!}}...
        @endif
    </p>
</div>
