@extends('layouts.admin')

@section('title', __('Administration'))

@section('main')
    <div id="admin-main-content" class="main-content">
        @yield('admin-main-content')
    </div>
@endsection

@section('admin-sidebar')
    @include('admin.partials.sidebar')
@endsection
