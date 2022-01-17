@extends('layouts.master')

@section('body')
    @yield('header')

    @yield('jumbo-top')

    <div class="container main-wrapper">
        <div class="d-flex flex-column-reverse flex-lg-row">
            <main role="main" class="flex-grow-1">
                @yield ('main')
            </main>

            @yield('sidebar')
        </div>
    </div>

    @yield('jumbo-down')

    @yield('footer')
@endsection

@section('offcanvas')
    <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="offcanvas-sidebar-user" aria-labelledby="offcanvas-label">
        <div class="offcanvas-header">
            <span class="offcanvas-title lead" id="offcanvas-label">{{ getSiteConfig('name') }}</span>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}}"></button>
        </div>

        <div class="offcanvas-body">
            @include('partials.navlinks')
        </div>
    </div>
@endsection
