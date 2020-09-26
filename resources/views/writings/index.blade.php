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
            @include('users.profile.banner')

            <p class="lead text-muted subtitle">
                @if (auth()->check() && auth()->user()->is($params['author']))
                    {{ __('My shelf') }}
                @else
                    {{ __('Shelved writings') }}
                @endif
            </p>
            <hr class="banner">
        @endif

        @include('writings.partials.toplinks')

        <div id="writing-list">
            @forelse ($writings as $writing)
                @include('writings.entry.index')
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
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection
