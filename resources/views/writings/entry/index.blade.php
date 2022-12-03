<div class="writing-entry card">
    @include('writings.entry.dropdown')
    @include('writings.entry.cover')
    @include('writings.entry.body')
</div>

@if ($params['writings_single_entry'] ?? false)
    @include('writings.entry.comments')
@endif
