@extends('layouts.admin')

@section('meta.title', $params['title'])

@section('header')
    @include('admin.partials.navbar')
@endsection

@section('main')
    <div id="admin-main-content" class="main-content">
        @yield('admin-main-content')
    </div>
@endsection

@section('footer')
    <footer></footer>
@endsection

@section('offcanvas')
    <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="offcanvas-sidebar-admin" aria-labelledby="offcanvas-label">
        <div class="offcanvas-header">
            <span class="offcanvas-title lead" id="offcanvas-label">{{ __('Administration') }}</span>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}}"></button>
        </div>

        <div class="offcanvas-body">
            @include('admin.partials.navlinks')
        </div>
    </div>
@endsection
