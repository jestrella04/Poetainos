@extends('layouts.index')

@section('meta.title', $params['title'])

@isset($params['canonical'])
    @section('link.canonical', $params['canonical'])
@endisset

@isset($user->extra_info['bio'])
    @section('meta.description', $user->extra_info['bio'])
@endisset

@isset($user->extra_info['interests'])
    @section('meta.keywords', $user->extra_info['interests'])
@endisset

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="users-main-content" class="main-content">
        @if (auth()->check() && auth()->user()->isAuthorBlocked($user))
            @include('partials.blocked')
        @else
            @include('users.profile.index')
        @endif
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection

