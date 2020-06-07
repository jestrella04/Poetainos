@extends('layouts.master')

@section('main-container')
    <main role="main" class="container-fluid admin-container">
        <div class="d-flex flex-wrap flex-lg-nowrap">
            @yield('admin-sidebar')

            <div class="flex-grow-1">
                @yield ('main')
            </div>
        </div>
    </main>
@endsection
