@extends('layouts.index')

@if ($writing->exists)
    @section('title', $params['title']['update'])
@else
    @section('title', $params['title']['create'])
@endif

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="writing-form-wrapper" class="main-content">
        @if ($writing->exists)
            <h3 class="all-caps">{{ $params['title']['update'] }}</h3>
        @else
            <h3 class="all-caps">{{ $params['title']['create'] }}</h3>
        @endif

        @include('writings.partials.form')
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
