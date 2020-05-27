@extends('layouts.index')

@section('title', $params['title'])

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="users-main-content" class="main-content">
        @include('users.profile.index')
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection

