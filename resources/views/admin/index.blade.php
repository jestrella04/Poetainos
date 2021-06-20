@extends('layouts.admin')

@section('meta.title', $params['title'])

@section('main')
    <nav>
        <button
            id="toggler"
            class="btn btn-sm btn-outline-dark"
            data-wh-target="#admin-side-menu"
            aria-label="{{ __('Toggle sidebar') }}">
            <i class="fa fa-bars"></i>
        </button>
    </nav>

    <div id="admin-main-content" class="main-content">
        @yield('admin-main-content')
    </div>
@endsection

@section('admin-sidebar')
    @include('admin.partials.sidebar')
@endsection
