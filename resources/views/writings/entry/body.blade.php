<div class="writing-body">
    <blockquote class="position-relative">
        @if ($params['single_entry'] ?? false)
            {!! nl2br(e($writing->text)) !!}
        @else
            {!! nl2br(e($writing->excerpt())) !!}

            <a href="{{ $writing->path() }}"
                @if (mb_strlen($writing->text) > 400)
                title="{{ __(':chars more characters.', ['chars' => mb_strlen($writing->text) - 400]) }}"
                @endif
                class="stretched-link">
                {{-- {{ __('Continue reading') }} --}}
            </a>
        @endif
    </blockquote>
</div>
