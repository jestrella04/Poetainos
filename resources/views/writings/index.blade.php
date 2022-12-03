@extends('layouts.index')

@section('meta.title', $params['title'])

@isset($params['description'])
    @section('meta.description', $params['description'])
@endisset

@isset($params['keywords'])
    @section('meta.keywords', $params['keywords'])
@endisset

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="writings-main-content" class="main-content">
        @if (isset($params['head_msg']))
            <p class="subtitle">{{ $params['head_msg'] }}</p>
        @endif

        @include('writings.partials.toplinks')

        <div id="writing-list">
            <div class="row masonry infinite-scroll" data-masonry='{"percentPosition": true }'>
                @forelse ($writings as $writing)
                    <div class="col-sm-6 col-lg-4 writing-entry-container entry-container">
                        @include('writings.entry.index')
                    </div>
                @empty
                    @if (isset($params['empty-head']))
                        @section('empty-head', $params['empty-head'])
                    @endif

                    @if (isset($params['empty-msg']))
                        @section('empty-msg', $params['empty-msg'])
                    @endif

                    @if (isset($params['empty-icon']))
                        @section('empty-icon', $params['empty-icon'])
                    @endif

                    @include('partials.empty')
                @endforelse
            </div>
        </div>

        {{ $writings->withQueryString()->links() }}

        @if ($writings->withQueryString()->hasMorePages())
            @include('partials.loading')
        @endif
    </div>
@endsection

{{-- @section('sidebar')
    @include('writings.entry.related')
@endsection --}}

@section('footer')
    @include('partials.footer')
@endsection
