@extends('layouts.master')

@section('main-container')
    <div class="container main-wrapper">
        <div class="d-flex flex-wrap flex-lg-nowrap">
            @yield('admin-sidebar')

            <main role="main" class="flex-grow-1">
                @yield ('main')
            </main>

            @yield('sidebar')
        </div>
    </div>
@endsection
