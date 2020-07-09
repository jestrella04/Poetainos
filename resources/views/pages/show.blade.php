@extends('layouts.index')

@section('meta.title', $params['title'])

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="pages-main-content" class="main-content">
        <div class="page-header">
            <h2 class="all-caps">{{ $page->title }}</h2>
        </div>

        <div class="page-body">
            @markdown($page->text)
        </div>
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection
