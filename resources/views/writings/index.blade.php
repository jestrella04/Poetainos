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
        @if (isset($params['section']) && 'shelf' === $params['section'] && isset($params['author']))
            <p class="subtitle">
                @if (auth()->check() && auth()->user()->is($params['author']))
                    {{ __('You are browsing the library of writings you have saved to your shelf.') }}
                @else
                    {{ __('You are browsing the library of writings bookmarked by @:user.', ['user' => $params['author']['username']]) }}
                @endif
            </p>
        @endif

        @include('writings.partials.toplinks')

        <div id="writing-list">
            <div class="row masonry" data-masonry='{"percentPosition": true }'>
                @forelse ($writings as $writing)
                    <div class="col-sm-6 col-lg-4 mb-4">
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
    </div>

    {{ $writings->withQueryString()->links() }}
@endsection

{{-- @section('sidebar')
    @include('writings.entry.related')
@endsection --}}

@section('footer')
    @include('partials.footer')
@endsection
