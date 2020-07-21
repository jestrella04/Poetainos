@extends('layouts.index')

@section('meta.title', $params['title'])

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
        @include('writings.entry.index')
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection
