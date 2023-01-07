@extends('layouts.index')

@section('meta.title', $params['title'])

@isset($params['canonical'])
    @section('link.canonical', $params['canonical'])
@endisset

@isset($writing->tags)
    @section('meta.keywords', $writing->tagsAsString())
@endisset

@if(! empty($writing->coverPath()))
    @section('meta.card', asset($writing->coverPath()))
@endif

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="writings-main-content" class="main-content">
        @if (auth()->check() && auth()->user()->isAuthorBlocked($writing->author))
            @include('partials.blocked')
        @else
            @include('writings.entry.index')
        @endif
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection
