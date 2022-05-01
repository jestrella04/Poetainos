@extends('layouts.index')

@section('meta.title', $params['title'])

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="explore-main-content" class="main-content">
        @include('blocks.main-categories')
        @include('blocks.alt-categories')
        @include('blocks.pop-tags')
        @include('blocks.featured-authors')
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection
