@extends('layouts.index')

@section('meta.title', $params['title'])

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="users-main-content" class="main-content">
        @include('users.partials.toplinks')

        <div id="user-list" class="row masonry infinite-scroll" data-masonry='{"percentPosition": true }'>
            @forelse ($users as $user)
                <div class="col-sm-6 entry-container">
                    @include('users.profile.banner')
                </div>
            @empty
                @include('partials.empty')
            @endforelse
        </div>

        {{ $users->withQueryString()->links() }}

        @if ($users->withQueryString()->hasMorePages())
            @include('partials.loading')
        @endif
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection
