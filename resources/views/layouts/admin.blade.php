@extends('layouts.master')

@section('body')
    @yield('header')

    @yield('jumbo-top')

    <div class="container-fluid admin-main-wrapper">
        <div class="d-flex flex-wrap flex-lg-nowrap">
            <main role="main" class="flex-grow-1">
                @yield ('main')
            </main>
        </div>
    </div>

    @yield('jumbo-down')

    @yield('footer')
    @yield('offcanvas')
@endsection
