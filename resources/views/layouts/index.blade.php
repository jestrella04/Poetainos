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
