@extends('layouts.index')

@section('title', $params['title'])

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="users-main-content" class="main-content">
        @include('users.partials.toplinks')

        <div id="user-list" class="d-flex flex-wrap justify-content-center">
            @forelse ($users as $user)
                @include('users.profile.banner')
            @empty
                @include('partials.empty')
            @endforelse
        </div>
    </div>

    {{ $users->withQueryString()->links() }}
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection
