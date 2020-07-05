@extends('layouts.index')

@section('meta.title', $params['title'])

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div class="main-content">
        @include('users.partials.form')
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection

