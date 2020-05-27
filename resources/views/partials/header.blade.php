@if ('home' === Route::current()->getName())
    @include('partials.header.full')
@else
    @include('partials.header.mini')
@endif
